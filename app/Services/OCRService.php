<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OCRService
{
    protected string $apiKey;
    protected string $apiUrl = 'https://api.ocr.space/parse/image';

    public function __construct()
    {
        $this->apiKey = config('services.ocr.space.key') ?? env('OCR_SPACE_API_KEY', '');
        
        if (empty($this->apiKey)) {
            Log::warning('OCR_SPACE_API_KEY not configured');
        } else {
            Log::debug('OCRService initialized', ['key_prefix' => substr($this->apiKey, 0, 5) . '...']);
        }
    }

    /**
     * Parse ID document image and extract structured data.
     *
     * @param  mixed  $imageData  Base64 string or UploadedFile object
     * @param  string  $idType  Type of ID (national, passport, driver_license, etc.)
     * @return array Extracted ID data or error response
     */
    public function parseIdDocument($imageData, string $idType = 'national'): array
    {
        try {
            // Determine if we have a file or base64 string
            $isFile = is_object($imageData) && method_exists($imageData, 'getPathname');
            
            if ($isFile) {
                Log::info('OCR.Space parseIdDocument with file upload', [
                    'file_size' => $imageData->getSize(),
                    'mime_type' => $imageData->getMimeType(),
                    'id_type' => $idType,
                ]);
            } else {
                // Legacy base64 handling
                $baseSize = is_string($imageData) ? strlen($imageData) : 0;
                Log::info('OCR.Space parseIdDocument with base64', [
                    'raw_size' => $baseSize,
                    'id_type' => $idType,
                ]);
            }

            // Keep OCR request timeout below PHP max_execution_time to avoid fatal timeout.
            $requestStartedAt = microtime(true);
            $response = Http::connectTimeout(8)->timeout(20);

            if ($isFile) {
                // Send actual file upload
                $response = $response
                    ->attach('file', fopen($imageData->getPathname(), 'r'), 'id-scan.jpg')
                    ->post($this->apiUrl, [
                        'apikey' => $this->apiKey,
                        'language' => 'eng',
                        'filetype' => 'jpg',
                        'OCREngine' => 2,
                        'scale' => 'true',
                        'detectOrientation' => 'true',
                        'isOverlayRequired' => 'true',
                    ]);
            } else {
                // Legacy base64 support
                $rawBase64 = $imageData;
                if (str_contains($imageData, 'data:image')) {
                    $rawBase64 = preg_replace('/^data:image\\/[^;]+;base64,/', '', $imageData);
                }
                
                $response = $response
                    ->post($this->apiUrl, [
                        'apikey' => $this->apiKey,
                        'base64Image' => 'data:image/jpeg;base64,' . $rawBase64,
                        'language' => 'eng',
                        'filetype' => 'jpg',
                        'OCREngine' => 2,
                        'scale' => 'true',
                        'detectOrientation' => 'true',
                        'isOverlayRequired' => 'true',
                    ]);
            }

            Log::info('OCR.Space API response', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'duration_ms' => (int) ((microtime(true) - $requestStartedAt) * 1000),
            ]);

            if (!$response->successful()) {
                Log::error('OCR.Space API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to process ID document (API error)',
                    'raw_text' => null,
                ];
            }

            $result = $response->json();

            $parsedResults = $result['ParsedResults'][0] ?? [];
            $parsedText = trim((string)($parsedResults['ParsedText'] ?? ''));
            $errorMessage = $result['ErrorMessage'] ?? ($parsedResults['ErrorMessage'] ?? null);

            Log::debug('OCR.Space API result', [
                'is_errored' => $result['IsErroredOnProcessing'] ?? false,
                'has_parsed_text' => !empty($parsedText),
                'error_message' => $errorMessage,
                'file_parse_exit_code' => $parsedResults['FileParseExitCode'] ?? null,
            ]);

            if ($result['IsErroredOnProcessing'] ?? false) {
                $errorMsg = $errorMessage ?? 'Failed to process ID document';
                Log::warning('OCR.Space processing error', [
                    'error' => $errorMsg,
                    'error_type' => gettype($errorMsg),
                ]);

                if (is_array($errorMsg)) {
                    $errorMsg = implode('; ', $errorMsg);
                }

                return [
                    'success' => false,
                    'message' => $errorMsg,
                    'raw_text' => null,
                ];
            }

            if (empty($parsedText)) {
                Log::warning('OCR.Space returned empty text', [
                    'full_response' => json_encode($result),
                ]);
                
                return [
                    'success' => false,
                    'message' => 'No text detected in image. Verify the ID quality and lighting.',
                    'raw_text' => null,
                ];
            }

            $extractedData = $this->extractIdData($parsedText, $idType);

            Log::info('ID data extracted', [
                'full_name' => $extractedData['full_name'] ?? '',
                'first_name' => $extractedData['first_name'] ?? '',
                'last_name' => $extractedData['last_name'] ?? '',
                'document_number' => $extractedData['document_number'] ?? '',
                'address' => $extractedData['address'] ?? '',
                'parsed_text_length' => strlen($parsedText),
            ]);

            return [
                'success' => true,
                'extracted_data' => $extractedData,
                'raw_text' => $parsedText,
                'confidence' => $result['Confidence'] ?? 0,
            ];
        } catch (ConnectionException $e) {
            Log::warning('OCR.Space request timeout/connection error', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'OCR request timed out. Please try again with a clearer image or retry in a few seconds.',
                'raw_text' => null,
            ];
        } catch (\Exception $e) {
            Log::error('OCR Service exception', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return [
                'success' => false,
                'message' => 'Error processing ID: ' . $e->getMessage(),
                'raw_text' => null,
            ];
        }
    }

    /**
     * Extract structured data from OCR text.
     * Supports National ID, Passport, Driver License.
     *
     * @param  string  $text  Raw OCR text
     * @param  string  $idType  Type of ID document
     * @return array Extracted fields
     */
    protected function extractIdData(string $text, string $idType = 'national'): array
    {
        $lines = array_filter(
            array_map('trim', explode("\n", $text)),
            fn($line) => !empty($line)
        );

        $normalizedIdType = $this->normalizeIdType($idType);
        $resolvedIdType = $this->resolveIdTypeFromText($lines, $normalizedIdType);

        $extracted = [
            'full_name' => '',
            'first_name' => '',
            'last_name' => '',
            'middle_name' => '',
            'document_number' => '',
            'document_type' => $resolvedIdType,
            'date_of_birth' => '',
            'expiration_date' => '',
            'gender' => '',
            'nationality' => '',
            'place_of_birth' => '',
            'address' => '',
        ];

        switch ($resolvedIdType) {
            case 'national':
                $extracted = $this->extractNationalIdData($lines, $extracted);
                break;
            case 'passport':
                $extracted = $this->extractPassportData($lines, $extracted);
                break;
            case 'driver_license':
                $extracted = $this->extractDriverLicenseData($lines, $extracted);
                break;
            default:
                $extracted = $this->extractNationalIdData($lines, $extracted);
                break;
        }

        return $extracted;
    }

    protected function extractNationalIdData(array $lines, array $extracted): array
    {
        $normalizedLines = array_values(array_filter(array_map(fn($line) => $this->normalizeOcrLine($line), $lines)));
        $fullText = implode(' ', $normalizedLines);
        $compactText = preg_replace('/\s+/', ' ', $fullText);

        // 0) Strongest path: capture values between known field labels
        $betweenLastName = $this->extractBetweenLabels($compactText, ['APELYIDO', 'LAST NAME'], ['MGA PANGALAN', 'GIVEN NAME']);
        $betweenGivenName = $this->extractBetweenLabels($compactText, ['MGA PANGALAN', 'GIVEN NAME'], ['GITNANG APELYIDO', 'MIDDLE NAME']);
        $betweenMiddleName = $this->extractBetweenLabels($compactText, ['GITNANG APELYIDO', 'MIDDLE NAME'], ['PETSA NG KAPANGANAKAN', 'DATE OF BIRTH']);

        $betweenLastName = $this->cleanPersonNameCandidate($betweenLastName);
        $betweenGivenName = $this->cleanPersonNameCandidate($betweenGivenName, true);
        $betweenMiddleName = $this->cleanPersonNameCandidate($betweenMiddleName);

        if (!empty($betweenLastName)) {
            $extracted['last_name'] = $betweenLastName;
        }
        if (!empty($betweenGivenName)) {
            $extracted['first_name'] = $betweenGivenName;
        }
        if (!empty($betweenMiddleName)) {
            $extracted['middle_name'] = $betweenMiddleName;
        }

        // 1) Prefer label-based extraction (most reliable for Philippine National ID layout)
        $labelLastName = $this->extractLabeledNameValue($normalizedLines, ['APELYIDO', 'LAST NAME']);
        $labelGivenName = $this->extractLabeledNameValue($normalizedLines, ['MGA PANGALAN', 'GIVEN NAME'], true);
        $labelMiddleName = $this->extractLabeledNameValue($normalizedLines, ['GITNANG APELYIDO', 'MIDDLE NAME']);

        if (!empty($labelLastName)) {
            $extracted['last_name'] = $labelLastName;
        }
        if (!empty($labelGivenName)) {
            $extracted['first_name'] = $labelGivenName;
        }
        if (!empty($labelMiddleName)) {
            $extracted['middle_name'] = $labelMiddleName;
        }

        if (!empty($extracted['last_name']) && !empty($extracted['first_name'])) {
            $extracted['full_name'] = trim($extracted['last_name'] . ', ' . $extracted['first_name']);
        }

        // Extract name from likely name lines (National ID often has surname + given names on separate lines)
        if (empty($extracted['full_name'])) {
            $nameCandidates = [];
            foreach ($normalizedLines as $line) {
                $cleaned = $this->cleanPersonNameCandidate($line, true);
                if (!empty($cleaned)) {
                    $nameCandidates[] = $cleaned;
                }
            }

            if (!empty($nameCandidates)) {
                // Prefer first 2 candidates: LastName then GivenName(s)
                if (empty($extracted['last_name'])) {
                    $extracted['last_name'] = $nameCandidates[0] ?? '';
                }
                if (empty($extracted['first_name'])) {
                    $extracted['first_name'] = $nameCandidates[1] ?? '';
                }
                if (empty($extracted['middle_name'])) {
                    $extracted['middle_name'] = $nameCandidates[2] ?? '';
                }

                if (!empty($extracted['last_name']) && !empty($extracted['first_name'])) {
                    $extracted['full_name'] = trim($extracted['last_name'] . ', ' . $extracted['first_name']);
                } elseif (!empty($extracted['last_name'])) {
                    $extracted['full_name'] = $extracted['last_name'];
                }
            }
        }

        // Fallback name extraction from comma format
        if (empty($extracted['full_name'])) {
            foreach ($normalizedLines as $line) {
                if (strlen($line) > 5 && preg_match('/^[A-Z\s,]+$/', $line) && strpos($line, ',') !== false) {
                    $extracted['full_name'] = trim($line);
                    break;
                }
            }
        }

        // Extract National ID number (supports dashed and undashed formats)
        if (preg_match('/\b(\d{4}[\-\s]?\d{4}[\-\s]?\d{4}[\-\s]?\d{4})\b/', $fullText, $matches)) {
            $extracted['document_number'] = preg_replace('/\s+/', '-', trim($matches[1]));
        } elseif (preg_match('/\b(\d{12,16})\b/', $fullText, $matches)) {
            $extracted['document_number'] = $matches[1];
        }

        // Extract date of birth (supports "NOVEMBER 11, 2004" and numeric formats)
        if (preg_match('/\b(JANUARY|FEBRUARY|MARCH|APRIL|MAY|JUNE|JULY|AUGUST|SEPTEMBER|OCTOBER|NOVEMBER|DECEMBER)\s+\d{1,2},\s*\d{4}\b/i', $fullText, $matches)) {
            $extracted['date_of_birth'] = trim($matches[0]);
        } elseif (preg_match('/\b(\d{2}[\/\-]\d{2}[\/\-]\d{4})\b/', $fullText, $matches)) {
            $extracted['date_of_birth'] = $this->normalizeDate($matches[1]);
        }

        // Extract expiration date
        if (preg_match('/expir[a-z]*.*?(\d{2}[\/\-]\d{2}[\/\-]\d{4})/i', $fullText, $matches)) {
            $extracted['expiration_date'] = $this->normalizeDate($matches[1]);
        }

        // Extract gender
        if (preg_match('/\b(M|F|MALE|FEMALE)\b/i', $fullText, $matches)) {
            $extracted['gender'] = strtoupper($matches[1][0]);
        }

        // Extract nationality
        if (preg_match('/nationality[:\s]+([A-Za-z\s]+)/i', $fullText, $matches)) {
            $extracted['nationality'] = trim($matches[1]);
        }

        // Extract address (prefer value near Tirahan/Address label)
        $addressFromPattern = $this->buildAddressFromNationalIdLines($normalizedLines, $compactText);
        $labeledAddress = $this->extractLabeledAddressValue($normalizedLines);
        if (empty($labeledAddress) && preg_match('/(PUROK\s*\d+[A-Z\s,.-]{5,}(?:CITY(?:\s+OF)?|MUNICIPALITY(?:\s+OF)?)[A-Z\s,.-]{3,})/i', $compactText, $m)) {
            $labeledAddress = trim((string)$m[1]);
        }
        if (empty($extracted['address'])) {
            if (!empty($addressFromPattern) && (substr_count($addressFromPattern, ',') >= 1 || strlen($addressFromPattern) > 12)) {
                $extracted['address'] = $addressFromPattern;
            } elseif (!empty($labeledAddress) && (substr_count($labeledAddress, ',') >= 1 || strlen($labeledAddress) > 12)) {
                $extracted['address'] = $labeledAddress;
            } else {
                $extracted['address'] = !empty($labeledAddress) ? $labeledAddress : $this->extractAddress($normalizedLines);
            }
        }

        // Final hard block for known OCR garbage tokens.
        if (in_array($extracted['first_name'], ['TIC', 'B NSA'], true)) {
            $extracted['first_name'] = '';
        }
        if (in_array($extracted['last_name'], ['ILL', 'EPUB'], true)) {
            $extracted['last_name'] = '';
        }

        if (empty($extracted['full_name']) && !empty($extracted['last_name']) && !empty($extracted['first_name'])) {
            $extracted['full_name'] = trim($extracted['last_name'] . ', ' . $extracted['first_name']);
        }

        return $extracted;
    }

    protected function extractPassportData(array $lines, array $extracted): array
    {
        $normalizedLines = array_values(array_filter(array_map(fn($line) => $this->normalizeOcrLine($line), $lines)));
        $fullText = implode(' ', $normalizedLines);

        // Parse MRZ names when present: P<COUNTRY<SURNAME<<GIVEN<NAMES
        foreach ($normalizedLines as $line) {
            $mrzLine = preg_replace('/\s+/', '', strtoupper((string)$line));
            if (!str_starts_with((string)$mrzLine, 'P<')) {
                continue;
            }

            if (preg_match('/^P<[A-Z<]{3}([A-Z<]+)$/', (string)$mrzLine, $m)) {
                $nameSection = trim((string)$m[1], '<');
                $parts = explode('<<', $nameSection);
                $surname = isset($parts[0]) ? str_replace('<', ' ', trim($parts[0])) : '';
                $given = isset($parts[1]) ? str_replace('<', ' ', trim($parts[1])) : '';

                if (!empty($surname) && empty($extracted['last_name'])) {
                    $extracted['last_name'] = preg_replace('/\s+/', ' ', $surname);
                }

                if (!empty($given) && empty($extracted['first_name'])) {
                    $extracted['first_name'] = preg_replace('/\s+/', ' ', $given);
                }

                break;
            }

            if (preg_match('/^P<[A-Z<]{3}([A-Z]{2,})(?:<<|<)([A-Z<]{2,})/i', (string)$mrzLine, $m)) {
                $surname = preg_replace('/<+/', ' ', trim((string)$m[1]));
                $given = preg_replace('/<+/', ' ', trim((string)$m[2]));

                if (!empty($surname) && empty($extracted['last_name'])) {
                    $extracted['last_name'] = trim((string)$surname);
                }

                if (!empty($given) && empty($extracted['first_name'])) {
                    $extracted['first_name'] = trim((string)$given);
                }

                break;
            }
        }

        if (empty($extracted['last_name']) || empty($extracted['first_name']) || empty($extracted['middle_name'])) {
            [$labelLastName, $labelFirstName, $labelMiddleName] = $this->extractPassportNamesFromLabels($normalizedLines);

            if (empty($extracted['last_name']) && !empty($labelLastName)) {
                $extracted['last_name'] = $labelLastName;
            }

            if (empty($extracted['first_name']) && !empty($labelFirstName)) {
                $extracted['first_name'] = $labelFirstName;
            }

            if (empty($extracted['middle_name']) && !empty($labelMiddleName)) {
                $extracted['middle_name'] = $labelMiddleName;
            }
        }

        // Extract passport number from labels first.
        if (preg_match('/\b(?:PASSPORT\s*(?:NO|NUMBER|#)?|DOCUMENT\s*NO|DOC\s*NO|NO)\s*[:\-]?\s*([A-Z0-9]{6,10})\b/i', $fullText, $matches)) {
            $extracted['document_number'] = strtoupper(trim($matches[1]));
        }

        // MRZ second line starts with passport number (commonly 9 chars).
        if (empty($extracted['document_number'])) {
            foreach ($normalizedLines as $line) {
                if (str_starts_with($line, 'P<')) {
                    continue;
                }

                if (preg_match('/^([A-Z0-9<]{8,10})[A-Z<0-9]{20,}$/', $line, $m)) {
                    $candidate = strtoupper(str_replace('<', '', $m[1]));
                    if (
                        !empty($candidate)
                        && strlen($candidate) >= 7
                        && !str_starts_with($candidate, 'PPHL')
                        && !str_contains($candidate, 'PHL')
                    ) {
                        $extracted['document_number'] = $candidate;
                        break;
                    }
                }
            }
        }

        // Generic fallback for alphanumeric passport-like numbers.
        if (empty($extracted['document_number']) && preg_match('/\b([A-Z]{1,2}\d{6,9}|\d{8,9})\b/', $fullText, $matches)) {
            $extracted['document_number'] = strtoupper(trim($matches[1]));
        }

        // Extract name from top lines when MRZ parsing did not provide it.
        foreach (array_slice($normalizedLines, 0, 6) as $line) {
            if (
                preg_match('/^[A-Z\s,]+$/', $line)
                && strlen($line) > 5
                && !preg_match('/\b(PASSPORT|REPUBLIC|PHILIPPINES|NATIONALITY|DATE|BIRTH|SEX|PLACE|AUTHORITY|TYPE)\b/', $line)
            ) {
                $extracted['full_name'] = trim($line);
                break;
            }
        }

        if (empty($extracted['last_name']) && empty($extracted['first_name']) && !empty($extracted['full_name'])) {
            $parts = explode(',', $extracted['full_name']);
            if (count($parts) >= 2) {
                $extracted['last_name'] = trim((string)$parts[0]);
                $extracted['first_name'] = trim((string)$parts[1]);
            } else {
                $tokens = preg_split('/\s+/', trim((string)$extracted['full_name']));
                if (count($tokens) >= 2) {
                    $extracted['first_name'] = trim((string)$tokens[0]);
                    $extracted['last_name'] = trim((string)implode(' ', array_slice($tokens, 1)));
                }
            }
        }

        // Prefer the structured MRZ name over any noisy header line.
        if (!empty($extracted['last_name']) && !empty($extracted['first_name'])) {
            $extracted['full_name'] = trim($extracted['last_name'] . ', ' . $extracted['first_name']);
        }

        if (empty($extracted['full_name']) && (!empty($extracted['last_name']) || !empty($extracted['first_name']))) {
            $extracted['full_name'] = trim($extracted['last_name'] . ', ' . $extracted['first_name'], ', ');
        }

        // Replace passport-header lines with structured name.
        if (
            !empty($extracted['full_name'])
            && preg_match('/\bPASSPORT\b/i', $extracted['full_name'])
            && (!empty($extracted['last_name']) || !empty($extracted['first_name']))
        ) {
            $extracted['full_name'] = trim($extracted['last_name'] . ', ' . $extracted['first_name'], ', ');
        }

        // Extract DOB from labels and generic numeric formats.
        if (preg_match('/\b(?:DATE\s*OF\s*BIRTH|BIRTH\s*DATE|DOB)\b[^0-9A-Z]*([0-9]{1,2}[\/\-][0-9]{1,2}[\/\-][0-9]{2,4})/i', $fullText, $matches)) {
            $extracted['date_of_birth'] = $this->normalizeDate($matches[1]);
        } elseif (preg_match('/\b(\d{2}[\/\-]\d{2}[\/\-]\d{4})\b/', $fullText, $matches)) {
            $extracted['date_of_birth'] = $this->normalizeDate($matches[1]);
        }

        // Extract expiration from labels and generic numeric formats.
        if (preg_match('/\b(?:DATE\s*OF\s*EXPIRY|DATE\s*OF\s*EXPIRATION|EXPIR(?:Y|ATION)?|VALID\s*UNTIL)\b[^0-9A-Z]*([0-9]{1,2}[\/\-][0-9]{1,2}[\/\-][0-9]{2,4})/i', $fullText, $matches)) {
            $extracted['expiration_date'] = $this->normalizeDate($matches[1]);
        } elseif (preg_match('/expir[a-z]*.*?(\d{2}[\/\-]\d{2}[\/\-]\d{4})/i', $fullText, $matches)) {
            $extracted['expiration_date'] = $this->normalizeDate($matches[1]);
        }

        if (preg_match('/\b(?:NATIONALITY|NAT)\s*[:\-]?\s*([A-Z]{3}|[A-Z\s]{4,})\b/i', $fullText, $matches)) {
            $extracted['nationality'] = trim((string)$matches[1]);
        }

        $extracted['place_of_birth'] = $this->extractPassportPlaceOfBirth($normalizedLines, $fullText);
        if (empty($extracted['address']) && !empty($extracted['place_of_birth'])) {
            $extracted['address'] = $extracted['place_of_birth'];
        }

        return $extracted;
    }

    protected function extractPassportNamesFromLabels(array $lines): array
    {
        $lastName = '';
        $firstName = '';
        $middleName = '';

        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];

            if (
                empty($lastName)
                && preg_match('/\b(APELYIDO|SURNAME)\b/i', $line)
                && !preg_match('/\b(MIDDLE\s*NAME|PANGGITNANG)\b/i', $line)
            ) {
                $lastName = $this->extractLabeledPassportValue($lines, $i, ['APELYIDO', 'SURNAME']);
            }

            if (
                empty($firstName)
                && preg_match('/\b(PANGALAN|GIVEN\s*NAMES?|GIVENNAME)\b/i', $line)
                && !preg_match('/\b(MIDDLE\s*NAME|PANGGITNANG)\b/i', $line)
            ) {
                $firstName = $this->extractLabeledPassportValue($lines, $i, ['PANGALAN', 'GIVEN NAMES', 'GIVEN NAME', 'GIVENNAME']);
            }

            if (empty($middleName) && preg_match('/\b(MIDDLE\s*NAME|PANGGITNANG\s+APELYIDO)\b/i', $line)) {
                $middleName = $this->extractLabeledPassportValue($lines, $i, ['MIDDLE NAME', 'PANGGITNANG APELYIDO']);
            }

            if (!empty($lastName) && !empty($firstName) && !empty($middleName)) {
                break;
            }
        }

        return [$lastName, $firstName, $middleName];
    }

    protected function extractLabeledPassportValue(array $lines, int $index, array $labels): string
    {
        $line = $lines[$index] ?? '';
        $labelPattern = implode('|', array_map(fn($v) => preg_quote(strtoupper($v), '/'), $labels));

        if (preg_match('/(?:' . $labelPattern . ')\s*[:\-]?\s*([A-Z][A-Z\s]{2,40})$/i', $line, $m)) {
            $candidate = $this->cleanPassportNameCandidate((string)$m[1]);
            if (!empty($candidate)) {
                return $candidate;
            }
        }

        for ($j = $index + 1; $j <= min($index + 2, count($lines) - 1); $j++) {
            $candidate = $this->cleanPassportNameCandidate((string)$lines[$j]);
            if (!empty($candidate)) {
                return $candidate;
            }
        }

        return '';
    }

    protected function cleanPassportNameCandidate(string $candidate): string
    {
        $candidate = strtoupper(trim($candidate));
        $candidate = preg_replace('/\s+/', ' ', $candidate);
        $candidate = trim((string)$candidate, " \t\n\r\0\x0B,:;.-");

        if (!preg_match('/^[A-Z\s]+$/', (string)$candidate)) {
            return '';
        }

        if (strlen((string)$candidate) < 2 || strlen((string)$candidate) > 50) {
            return '';
        }

        if (preg_match('/\b(PASSPORT|REPUBLIC|PHILIPPINES|PLACE|BIRTH|DATE|NATIONALITY|POB|SEX|AUTHORITY|TYPE|DOCUMENT|NO)\b/', (string)$candidate)) {
            return '';
        }

        return (string)$candidate;
    }

    protected function extractPassportPlaceOfBirth(array $lines, string $fullText): string
    {
        foreach ($lines as $index => $line) {
            if (!preg_match('/\b(PLACE\s+OF\s+BIRTH|BIRTH\s+PLACE|POB)\b/i', $line)) {
                continue;
            }

            if (preg_match('/\b(PLACE\s+OF\s+BIRTH|BIRTH\s+PLACE|POB)\b\s*[:\-]?\s*([A-Z0-9\s,.-]{3,})$/i', $line, $m)) {
                $candidate = $this->cleanPassportLocationCandidate((string)$m[2]);
                if (!empty($candidate)) {
                    return $candidate;
                }
            }

            for ($i = $index + 1; $i <= min($index + 2, count($lines) - 1); $i++) {
                $candidate = $this->cleanPassportLocationCandidate((string)$lines[$i]);
                if (!empty($candidate)) {
                    return $candidate;
                }
            }
        }

        if (preg_match('/\b([A-Z][A-Z\s.-]{3,40}\s+CITY|CITY\s+OF\s+[A-Z][A-Z\s.-]{2,40})\b/i', $fullText, $m)) {
            return $this->cleanPassportLocationCandidate((string)$m[1]);
        }

        return '';
    }

    protected function cleanPassportLocationCandidate(string $candidate): string
    {
        $candidate = strtoupper(trim($candidate));
        $candidate = preg_replace('/\s+/', ' ', $candidate);
        $candidate = trim((string)$candidate, " \t\n\r\0\x0B,:;.-");

        if (empty($candidate)) {
            return '';
        }

        if (preg_match('/\b(PASSPORT|REPUBLIC|PHILIPPINES|NATIONALITY|DATE|BIRTH|SEX|AUTHORITY|TYPE|P<)\b/', $candidate)) {
            return '';
        }

        return $candidate;
    }

    protected function normalizeIdType(string $idType): string
    {
        $normalized = strtolower(trim($idType));

        return match ($normalized) {
            'passport', 'pass port', 'password' => 'passport',
            'driver', 'driver_license', 'drivers_license', 'driver-license' => 'driver_license',
            'auto', 'automatic', '' => 'auto',
            default => 'national',
        };
    }

    protected function resolveIdTypeFromText(array $lines, string $requestedType): string
    {
        if (in_array($requestedType, ['passport', 'driver_license', 'national'], true)) {
            return $requestedType;
        }

        $fullText = strtoupper(implode(' ', $lines));

        if (preg_match('/\bPASSPORT\b/', $fullText)) {
            return 'passport';
        }

        foreach ($lines as $line) {
            $normalized = strtoupper(trim((string)$line));
            if (str_starts_with($normalized, 'P<') || str_contains($normalized, '<<')) {
                return 'passport';
            }
        }

        if (preg_match('/\b(DRIVER|LICENSE|LICENCE|DL)\b/', $fullText)) {
            return 'driver_license';
        }

        return 'national';
    }

    protected function extractDriverLicenseData(array $lines, array $extracted): array
    {
        $normalizedLines = array_values(array_filter(array_map(fn($line) => $this->normalizeOcrLine($line), $lines)));
        $fullText = implode(' ', $normalizedLines);

        // 1) Driver license number
        $documentNumber = $this->extractLabeledDriverLicenseNumber($normalizedLines);
        if (empty($documentNumber)) {
            $numberPatterns = [
                '/\b([A-Z]\d{2}-\d{2}-\d{6})\b/',
                '/\b([A-Z]\d{2}-\d{2}-\d{2}-\d{6})\b/',
                '/\b([A-Z0-9]{2,4}-[A-Z0-9]{2,4}-[A-Z0-9]{4,8})\b/',
                '/\b([A-Z0-9]{8,16})\b/',
            ];

            foreach ($numberPatterns as $pattern) {
                if (preg_match($pattern, $fullText, $matches)) {
                    $candidate = $this->cleanDriverLicenseNumber((string)$matches[1]);
                    if ($this->isLikelyDriverLicenseNumber($candidate)) {
                        $documentNumber = $candidate;
                        break;
                    }
                }
            }
        }
        if (!empty($documentNumber)) {
            $extracted['document_number'] = $documentNumber;
        }

        if (!empty($extracted['document_number']) && preg_match('/\bAGENCY\s*CODE\s*[:\-]?\s*([A-Z0-9]{2,4})\b/', $fullText, $agencyMatch)) {
            $agencyCode = strtoupper(trim((string)$agencyMatch[1]));
            if (preg_match('/^[A-Z]\d{2}$/', $agencyCode) && preg_match('/^\d{3}-\d{2}-\d{6}$/', $extracted['document_number'])) {
                $extracted['document_number'] = $agencyCode . substr($extracted['document_number'], 3);
            }
        }

        // 2) Name fields
        $lastName = $this->extractLabeledDriverNameValue($normalizedLines, ['SURNAME', 'LAST NAME', 'APELYIDO']);
        $firstName = $this->extractLabeledDriverNameValue($normalizedLines, ['FIRST NAME', 'GIVEN NAME', 'MGA PANGALAN'], true);
        $middleName = $this->extractLabeledDriverNameValue($normalizedLines, ['MIDDLE NAME', 'MIDDLE INITIAL', 'GITNANG APELYIDO'], true);

        if (empty($lastName) || empty($firstName)) {
            $fallbackNames = $this->extractDriverNamesFromCommaLine($normalizedLines);
            if (empty($lastName) && !empty($fallbackNames['last_name'])) {
                $lastName = $fallbackNames['last_name'];
            }
            if (empty($firstName) && !empty($fallbackNames['first_name'])) {
                $firstName = $fallbackNames['first_name'];
            }
            if (empty($middleName) && !empty($fallbackNames['middle_name'])) {
                $middleName = $fallbackNames['middle_name'];
            }
        }

        if (empty($lastName) || empty($firstName)) {
            $headerBlockNames = $this->extractDriverNameFromHeaderBlock($normalizedLines);
            if (empty($lastName) && !empty($headerBlockNames['last_name'])) {
                $lastName = $headerBlockNames['last_name'];
            }
            if (empty($firstName) && !empty($headerBlockNames['first_name'])) {
                $firstName = $headerBlockNames['first_name'];
            }
            if (empty($middleName) && !empty($headerBlockNames['middle_name'])) {
                $middleName = $headerBlockNames['middle_name'];
            }
        }

        if (!empty($lastName)) {
            $extracted['last_name'] = $lastName;
        }
        if (!empty($firstName)) {
            $extracted['first_name'] = $firstName;
        }
        if (!empty($middleName)) {
            $extracted['middle_name'] = $middleName;
        }

        if (!empty($extracted['last_name']) && !empty($extracted['first_name'])) {
            $extracted['full_name'] = trim(
                $extracted['last_name']
                . ', '
                . $extracted['first_name']
                . (!empty($extracted['middle_name']) ? ' ' . $extracted['middle_name'] : '')
            );
        } elseif (!empty($extracted['first_name'])) {
            $extracted['full_name'] = trim($extracted['first_name'] . (!empty($extracted['middle_name']) ? ' ' . $extracted['middle_name'] : ''));
        } elseif (!empty($extracted['last_name'])) {
            $extracted['full_name'] = $extracted['last_name'];
        }

        // 3) Dates
        $dateOfBirth = $this->extractLabeledDriverDate($normalizedLines, ['DATE OF BIRTH', 'BIRTH DATE', 'BIRTHDATE', 'DOB']);
        if (empty($dateOfBirth) && preg_match('/\b(\d{2}[\/\-]\d{2}[\/\-]\d{4}|\d{4}[\/\-]\d{2}[\/\-]\d{2})\b/', $fullText, $matches)) {
            $dateOfBirth = $this->normalizeDate((string)$matches[1]);
        }
        if (!empty($dateOfBirth)) {
            $extracted['date_of_birth'] = $dateOfBirth;
        }

        $expirationDate = $this->extractLabeledDriverDate($normalizedLines, ['EXPIRATION', 'EXPIRY', 'EXPIRES', 'VALID UNTIL', 'VALIDITY']);
        if (!empty($expirationDate)) {
            $extracted['expiration_date'] = $expirationDate;
        }

        // 4) Gender and nationality
        if (preg_match('/\b(?:SEX|GENDER)\s*[:\-]?\s*(MALE|FEMALE|M|F)\b/i', $fullText, $matches)) {
            $gender = strtoupper((string)$matches[1]);
            $extracted['gender'] = $gender === 'M' ? 'MALE' : ($gender === 'F' ? 'FEMALE' : $gender);
        }

        if (preg_match('/\bNATIONALITY\s*[:\-]?\s*([A-Z]{3,20})\b/i', $fullText, $matches)) {
            $extracted['nationality'] = strtoupper(trim((string)$matches[1]));
        }

        // 5) Address and place of birth
        $driverAddress = $this->extractLabeledDriverAddress($normalizedLines);
        if (!empty($driverAddress)) {
            $extracted['address'] = $driverAddress;
        } elseif (empty($extracted['address'])) {
            $fallbackAddress = $this->extractAddress($normalizedLines);
            if (!empty($fallbackAddress)) {
                $extracted['address'] = $fallbackAddress;
            }
        }

        $placeOfBirth = $this->extractLabeledDriverNameValue($normalizedLines, ['PLACE OF BIRTH', 'BIRTH PLACE', 'BIRTHPLACE'], true);
        if (!empty($placeOfBirth)) {
            $extracted['place_of_birth'] = $placeOfBirth;
        }

        return $extracted;
    }

    protected function extractLabeledDriverLicenseNumber(array $lines): string
    {
        $labels = ['LICENSE NO', 'LICENCE NO', 'DL NO', 'LICENSE NUMBER', 'LICENCE NUMBER', 'NO.'];

        foreach ($lines as $line) {
            foreach ($labels as $label) {
                if (!str_contains($line, $label)) {
                    continue;
                }

                if (preg_match('/(?:LICENSE\s*(?:NO|NUMBER)?|LICENCE\s*(?:NO|NUMBER)?|DL\s*NO|NO\.)\s*[:#\-]?\s*([A-Z0-9\-\s]{6,24})/', $line, $matches)) {
                    $candidate = $this->cleanDriverLicenseNumber((string)$matches[1]);
                    if ($this->isLikelyDriverLicenseNumber($candidate)) {
                        return $candidate;
                    }
                }
            }
        }

        return '';
    }

    protected function extractLabeledDriverNameValue(array $lines, array $labels, bool $allowMultiWord = false): string
    {
        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];
            $labelMatched = false;

            foreach ($labels as $label) {
                if (str_contains($line, strtoupper($label))) {
                    $labelMatched = true;
                    $pattern = '/(?:' . preg_quote(strtoupper($label), '/') . ')\s*[:\-]?\s*([A-Z][A-Z\s]{1,40})$/';
                    if (preg_match($pattern, $line, $matches)) {
                        $sameLine = $this->cleanDriverNameCandidate((string)$matches[1], $allowMultiWord);
                        if (!empty($sameLine)) {
                            return $sameLine;
                        }
                    }
                    break;
                }
            }

            if (!$labelMatched) {
                continue;
            }

            for ($j = $i + 1; $j <= min($i + 4, count($lines) - 1); $j++) {
                $candidate = $this->cleanDriverNameCandidate((string)$lines[$j], $allowMultiWord);
                if (!empty($candidate)) {
                    return $candidate;
                }
            }
        }

        return '';
    }

    protected function extractDriverNamesFromCommaLine(array $lines): array
    {
        foreach ($lines as $line) {
            if (!preg_match('/^([A-Z][A-Z\s]{1,35}),\s*([A-Z][A-Z\s]{1,35})(?:\s+([A-Z][A-Z\s]{1,35}))?$/', $line, $matches)) {
                continue;
            }

            $lastName = $this->cleanDriverNameCandidate((string)$matches[1]);
            $firstName = $this->cleanDriverNameCandidate((string)$matches[2], true);
            $middleName = !empty($matches[3]) ? $this->cleanDriverNameCandidate((string)$matches[3], true) : '';

            if (!empty($lastName) && !empty($firstName)) {
                return [
                    'last_name' => $lastName,
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                ];
            }
        }

        return [
            'last_name' => '',
            'first_name' => '',
            'middle_name' => '',
        ];
    }

    protected function extractDriverNameFromHeaderBlock(array $lines): array
    {
        for ($i = 0; $i < count($lines); $i++) {
            if (!preg_match('/LAST\s*NAME\s*,?\s*FIRST\s*NAME\s*,?\s*MIDDLE\s*NAME/', $lines[$i])) {
                continue;
            }

            for ($j = $i + 1; $j <= min($i + 3, count($lines) - 1); $j++) {
                $candidateLine = trim((string)$lines[$j]);
                if (empty($candidateLine) || !str_contains($candidateLine, ',')) {
                    continue;
                }

                if (preg_match('/^([A-Z][A-Z\s]{1,35}),\s*([A-Z][A-Z\s]{1,35})(?:\s+([A-Z][A-Z\s]{1,35}))?$/', $candidateLine, $matches)) {
                    $lastName = $this->cleanDriverNameCandidate((string)$matches[1]);
                    $firstName = $this->cleanDriverNameCandidate((string)$matches[2], true);
                    $middleName = !empty($matches[3]) ? $this->cleanDriverNameCandidate((string)$matches[3], true) : '';

                    if (!empty($lastName) && !empty($firstName)) {
                        return [
                            'last_name' => $lastName,
                            'first_name' => $firstName,
                            'middle_name' => $middleName,
                        ];
                    }
                }
            }
        }

        return [
            'last_name' => '',
            'first_name' => '',
            'middle_name' => '',
        ];
    }

    protected function cleanDriverNameCandidate(string $candidate, bool $allowMultiWord = false): string
    {
        $candidate = $this->cleanPersonNameCandidate($candidate, $allowMultiWord);
        if (empty($candidate)) {
            return '';
        }

        if ($this->isDriverNameNoise($candidate)) {
            return '';
        }

        return $candidate;
    }

    protected function isDriverNameNoise(string $value): bool
    {
        $value = strtoupper(trim($value));
        if (empty($value)) {
            return true;
        }

        $noisePatterns = [
            '/\b(NATIONALITY|NATI0NALITY|NAVONSITY|NATION|SEX|DATE OF BIRTH|BIRTH|ADDRESS|LICENSE|LICENCE|EXPIRATION|EXPIRY|AGENCY|CODE|EYES|BLOOD|DL CODES|CONDITIONS)\b/',
            '/\b(PHIL|PHL|REPUBLIC|PHILIPPINES|LAND TRANSPORTATION|LTO)\b/',
            '/\b(ROAD|STREET|CITY|BATANGAS|BLOCK|LOT)\b/',
        ];

        foreach ($noisePatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }

    protected function extractLabeledDriverDate(array $lines, array $labels): string
    {
        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];
            $matchedLabel = false;

            foreach ($labels as $label) {
                if (!str_contains($line, strtoupper($label))) {
                    continue;
                }

                $matchedLabel = true;
                if (preg_match('/\b(\d{2}[\/\-]\d{2}[\/\-]\d{4}|\d{4}[\/\-]\d{2}[\/\-]\d{2})\b/', $line, $matches)) {
                    return $this->normalizeDate((string)$matches[1]);
                }

                break;
            }

            if (!$matchedLabel) {
                continue;
            }

            for ($j = $i + 1; $j <= min($i + 2, count($lines) - 1); $j++) {
                if (preg_match('/\b(\d{2}[\/\-]\d{2}[\/\-]\d{4}|\d{4}[\/\-]\d{2}[\/\-]\d{2})\b/', $lines[$j], $matches)) {
                    return $this->normalizeDate((string)$matches[1]);
                }
            }
        }

        return '';
    }

    protected function extractLabeledDriverAddress(array $lines): string
    {
        $addressParts = [];

        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];
            if (!str_contains($line, 'ADDRESS')) {
                continue;
            }

            $sameLine = preg_replace('/^.*?ADDRESS\s*[:\-]?\s*/', '', $line);
            $sameLine = $this->cleanAddressCandidate((string)$sameLine);
            if (!empty($sameLine)) {
                $addressParts[] = $sameLine;
            }

            for ($j = $i + 1; $j <= min($i + 3, count($lines) - 1); $j++) {
                if (preg_match('/\b(DATE OF BIRTH|BIRTH|EXPIRATION|EXPIRY|SEX|GENDER|NATIONALITY|LICENSE|LICENCE)\b/', $lines[$j])) {
                    break;
                }

                $candidate = $this->cleanAddressCandidate((string)$lines[$j]);
                if (!empty($candidate)) {
                    $addressParts[] = $candidate;
                }
            }

            if (!empty($addressParts)) {
                break;
            }
        }

        if (empty($addressParts)) {
            return '';
        }

        return implode(', ', array_slice(array_values(array_unique($addressParts)), 0, 3));
    }

    protected function cleanDriverLicenseNumber(string $value): string
    {
        $value = strtoupper(trim($value));
        $value = preg_replace('/\s+/', '', $value);
        $value = preg_replace('/[^A-Z0-9\-]/', '', (string)$value);
        return trim((string)$value, '-');
    }

    protected function isLikelyDriverLicenseNumber(string $value): bool
    {
        if (empty($value)) {
            return false;
        }

        if (strlen($value) < 6 || strlen($value) > 20) {
            return false;
        }

        if (!preg_match('/\d/', $value)) {
            return false;
        }

        if (preg_match('/^\d{2}[\-\/]\d{2}[\-\/]\d{4}$/', $value)) {
            return false;
        }

        return true;
    }

    protected function extractAddress(array $lines): string
    {
        $addressLines = [];

        foreach ($lines as $line) {
            if (preg_match('/\b(TIRAHAN|ADDRESS|PUROK|BARANGAY|BRGY|CITY|MUNICIPALITY|PROVINCE|REGION)\b/i', $line)) {
                $cleanLine = preg_replace('/\b(TIRAHAN|ADDRESS)\b[:\s]*/i', '', $line);
                $cleanLine = trim($cleanLine, " \t\n\r\0\x0B,:;");
                if (!empty($cleanLine)) {
                    $addressLines[] = $cleanLine;
                }
            }
        }

        if (empty($addressLines)) {
            foreach (array_slice($lines, 5) as $line) {
                if (strlen($line) > 4 && !preg_match('/^\d+$/', $line) && !$this->isLikelyNameLine($line)) {
                    $addressLines[] = trim($line);
                    if (count($addressLines) >= 3) {
                        break;
                    }
                }
            }
        }

        return implode(', ', array_slice(array_values(array_unique($addressLines)), 0, 3));
    }

    protected function normalizeOcrLine(string $line): string
    {
        $line = strtoupper(trim($line));
        $line = preg_replace('/\s+/', ' ', $line);
        return trim((string)$line);
    }

    protected function isLikelyNameLine(string $line): bool
    {
        if (strlen($line) < 2 || strlen($line) > 40) {
            return false;
        }

        if (!preg_match('/^[A-Z\s]+$/', $line)) {
            return false;
        }

        $noiseWords = [
            'REPUBLIKA', 'PILIPINAS', 'PHILIPPINES', 'NATIONAL', 'IDENTIFICATION',
            'CARD', 'ADDRESS', 'TIRAHAN', 'CITY', 'MUNICIPALITY', 'PROVINCE', 'REGION',
            'BIRTH', 'DATE', 'SEX', 'MALE', 'FEMALE', 'PSN', 'PIN',
            'EPUB', 'NSA'
        ];

        foreach ($noiseWords as $noiseWord) {
            if (str_contains($line, $noiseWord)) {
                return false;
            }
        }

        $wordCount = count(array_filter(explode(' ', $line)));
        return $wordCount >= 1 && $wordCount <= 4;
    }

    protected function extractLabeledNameValue(array $lines, array $labelKeywords, bool $allowMultiWord = false): string
    {
        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];
            $hasLabel = false;

            foreach ($labelKeywords as $keyword) {
                if (str_contains($line, strtoupper($keyword))) {
                    $hasLabel = true;
                    break;
                }
            }

            if (!$hasLabel) {
                continue;
            }

            // If value is on same line after the label, capture tail text.
            if (preg_match('/(?:LAST\s*NAME|GIVEN\s*NAME|MIDDLE\s*NAME|APELYIDO|MGA\s+PANGALAN|GITNANG\s+APELYIDO)\s*[:\-]?\s*([A-Z][A-Z\s]{1,40})$/', $line, $m)) {
                $candidate = $this->cleanPersonNameCandidate($m[1], $allowMultiWord);
                if (!empty($candidate)) {
                    return $candidate;
                }
            }

            // Otherwise take nearest valid next line.
            for ($j = $i + 1; $j <= min($i + 8, count($lines) - 1); $j++) {
                $candidate = $this->cleanPersonNameCandidate($lines[$j], $allowMultiWord);
                if (!empty($candidate)) {
                    return $candidate;
                }
            }
        }

        return '';
    }

    protected function cleanPersonNameCandidate(string $candidate, bool $allowMultiWord = false): string
    {
        $candidate = strtoupper(trim($candidate));
        $candidate = preg_replace('/\s+/', ' ', $candidate);
        $candidate = trim((string)$candidate, " \t\n\r\0\x0B,:;.-");

        if (!preg_match('/^[A-Z\s]+$/', (string)$candidate)) {
            return '';
        }

        if (strlen((string)$candidate) < 4 || strlen((string)$candidate) > 40) {
            return '';
        }

        if (substr_count((string)$candidate, 'A') + substr_count((string)$candidate, 'E') + substr_count((string)$candidate, 'I') + substr_count((string)$candidate, 'O') + substr_count((string)$candidate, 'U') < 2) {
            return '';
        }

        $wordCount = count(array_filter(explode(' ', (string)$candidate)));
        if (!$allowMultiWord && $wordCount > 2) {
            return '';
        }
        if ($allowMultiWord && $wordCount > 4) {
            return '';
        }

        if (!$this->isLikelyNameLine((string)$candidate)) {
            return '';
        }

        return (string)$candidate;
    }

    protected function extractBetweenLabels(string $text, array $startLabels, array $endLabels): string
    {
        $startPattern = implode('|', array_map(fn($v) => preg_quote(strtoupper($v), '/'), $startLabels));
        $endPattern = implode('|', array_map(fn($v) => preg_quote(strtoupper($v), '/'), $endLabels));

        if (preg_match('/(?:' . $startPattern . ')\s*[:\-\/\|]*\s*(.*?)\s*(?:' . $endPattern . ')/i', $text, $m)) {
            return trim((string)$m[1]);
        }

        return '';
    }

    protected function extractLabeledAddressValue(array $lines): string
    {
        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];
            if (!str_contains($line, 'TIRAHAN') && !str_contains($line, 'ADDRESS')) {
                continue;
            }

            // Same-line value after label
            if (preg_match('/(?:TIRAHAN|ADDRESS)\s*[\/\|]?\s*(?:ADDRESS)?\s*[:\-]?\s*([A-Z0-9\s,.-]{8,})$/', $line, $m)) {
                $sameLine = $this->cleanAddressCandidate((string)$m[1]);
                if (!empty($sameLine)) {
                    return $sameLine;
                }
            }

            // Next likely line(s)
            for ($j = $i + 1; $j <= min($i + 3, count($lines) - 1); $j++) {
                $candidate = $this->cleanAddressCandidate((string)$lines[$j]);
                if (!empty($candidate)) {
                    return $candidate;
                }
            }
        }

        return '';
    }

    protected function cleanAddressCandidate(string $candidate): string
    {
        $candidate = strtoupper(trim($candidate));
        $candidate = preg_replace('/\s+/', ' ', $candidate);
        $candidate = trim((string)$candidate, " \t\n\r\0\x0B,:;");

        // Reject known OCR garbage values.
        if (preg_match('/^(CLIKANGPILIPINA|PILIPINAS|REPUBLIKANGPILIPINAS)$/', $candidate)) {
            return '';
        }

        // Reject long, single-token gibberish with no separators.
        if (preg_match('/^[A-Z]{12,}$/', $candidate) && !preg_match('/\b(PUROK|BARANGAY|BRGY|CITY|MUNICIPALITY|PROVINCE)\b/', $candidate)) {
            return '';
        }

        return $candidate;
    }

    protected function buildAddressFromNationalIdLines(array $lines, string $text): string
    {
        $parts = [];

        $collecting = false;
        $sawAddressLabel = false;
        $collectedLines = [];

        foreach ($lines as $line) {
            if (preg_match('/\b(TIRAHAN|ADDRESS)\b/i', $line)) {
                $collecting = true;
                $sawAddressLabel = true;

                $inlineValue = preg_replace('/^.*?\b(TIRAHAN|ADDRESS)\b\s*[\/\|]?\s*(?:ADDRESS)?\s*[:\-]?\s*/i', '', $line);
                $inlineValue = $this->cleanAddressCandidate($inlineValue);
                if (!empty($inlineValue)) {
                    $collectedLines[] = $inlineValue;
                }

                continue;
            }

            if (!$collecting) {
                continue;
            }

            if (preg_match('/\b(PETSA NG KAPANGANAKAN|DATE OF BIRTH|DOB|BIRTH|MGA PANGALAN|GIVEN NAME|APELYIDO|LAST NAME)\b/i', $line)) {
                break;
            }

            $candidate = $this->cleanAddressCandidate($line);
            if (!empty($candidate)) {
                $collectedLines[] = $candidate;
            }

            if (count($collectedLines) >= 3) {
                break;
            }
        }

        if ($sawAddressLabel && !empty($collectedLines)) {
            $joined = implode(', ', array_values(array_unique($collectedLines)));
            if (!empty($joined)) {
                $parts[] = $joined;
            }
        }

        if (preg_match('/\bPUROK\s*\d+\b/i', $text, $m)) {
            $parts[] = strtoupper(trim($m[0]));
        }

        $parts = array_values(array_unique(array_filter($parts)));
        if (empty($parts)) {
            return '';
        }

        // Prefer the first full address block if present.
        if (count($parts) === 1) {
            return $parts[0];
        }

        return implode(', ', $parts);
    }

    protected function normalizeDate(string $date): string
    {
        // Convert various date formats to YYYY-MM-DD
        $date = trim($date);

        // Replace common separators
        $date = str_replace(['/', '-'], '-', $date);
        $parts = explode('-', $date);

        if (count($parts) !== 3) {
            return $date;
        }

        [$part1, $part2, $part3] = $parts;

        // Detect format
        if (strlen($part3) === 4) {
            // Already in MM-DD-YYYY or DD-MM-YYYY
            // Try to detect which by checking if part1 > 12
            if ((int)$part1 > 12) {
                // DD-MM-YYYY
                return "{$part3}-{$part2}-{$part1}";
            } else {
                // MM-DD-YYYY
                return "{$part3}-{$part1}-{$part2}";
            }
        }

        return $date;
    }
}
