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
            case 'umid':
                $extracted = $this->extractUmidData($lines, $extracted);
                break;
            case 'voters_id':
                $extracted = $this->extractVotersIdData($lines, $extracted);
                break;
            case 'senior_citizen_id':
                $extracted = $this->extractSeniorCitizenIdData($lines, $extracted);
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

    /**
     * Philippine Unified Multi-Purpose ID (UMID): English labels, CRN, address often ends with PHL + ZIP.
     */
    protected function extractUmidData(array $lines, array $extracted): array
    {
        $normalizedLines = array_values(array_filter(array_map(function ($line) {
            return $this->normalizeUmidLabelTypos($this->normalizeOcrLine($line));
        }, $lines)));
        $fullText = implode(' ', $normalizedLines);
        $compactText = preg_replace('/\s+/', ' ', $fullText);

        $lastName = $this->extractLabeledDriverNameValue($normalizedLines, ['SURNAME', 'LAST NAME', 'APELYIDO']);
        $firstName = $this->extractLabeledDriverNameValue($normalizedLines, ['GIVEN NAME', 'FIRST NAME', 'MGA PANGALAN'], true);
        $middleName = $this->extractLabeledDriverNameValue($normalizedLines, ['MIDDLE NAME', 'MIDDLE INITIAL', 'GITNANG APELYIDO'], true);

        if (empty($lastName) || empty($firstName)) {
            $commaNames = $this->extractDriverNamesFromCommaLine($normalizedLines);
            if (empty($lastName) && !empty($commaNames['last_name'])) {
                $lastName = $commaNames['last_name'];
            }
            if (empty($firstName) && !empty($commaNames['first_name'])) {
                $firstName = $commaNames['first_name'];
            }
            if (empty($middleName) && !empty($commaNames['middle_name'])) {
                $middleName = $commaNames['middle_name'];
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

        if ($lastName === '' || $firstName === '') {
            $fromCompactNames = $this->extractUmidNamesFromCompact($compactText);
            if ($lastName === '' && !empty($fromCompactNames['last_name'])) {
                $lastName = $fromCompactNames['last_name'];
            }
            if ($firstName === '' && !empty($fromCompactNames['first_name'])) {
                $firstName = $fromCompactNames['first_name'];
            }
            if ($middleName === '' && !empty($fromCompactNames['middle_name'])) {
                $middleName = $fromCompactNames['middle_name'];
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
        }

        if (!empty($extracted['last_name']) && !empty($extracted['first_name'])) {
            $extracted['full_name'] = trim(
                $extracted['last_name']
                . ', '
                . $extracted['first_name']
                . (!empty($extracted['middle_name']) ? ' ' . $extracted['middle_name'] : '')
            );
        }

        if (preg_match('/\b(?:CRN|COMMON\s+REFERENCE(?:\s+NUMBER)?)\s*[#:\-]?\s*(\d{4}[\-\s]?\d{7}[\-\s]?\d)\b/i', $compactText, $m)) {
            $extracted['document_number'] = preg_replace('/\s+/', '', (string) $m[1]);
        } elseif (preg_match('/\b(\d{4}[\-\s]?\d{7}[\-\s]?\d)\b/', $compactText, $m)) {
            $extracted['document_number'] = preg_replace('/\s+/', '', (string) $m[1]);
        }

        $dateOfBirth = $this->extractLabeledDriverDate($normalizedLines, ['DATE OF BIRTH', 'BIRTH DATE', 'BIRTHDATE', 'DOB', 'PETSA NG KAPANGANAKAN']);
        if (empty($dateOfBirth) && preg_match('/\b(\d{2}[\/\-]\d{2}[\/\-]\d{4}|\d{4}[\/\-]\d{2}[\/\-]\d{2})\b/', $fullText, $matches)) {
            $dateOfBirth = $this->normalizeDate((string) $matches[1]);
        }
        if (!empty($dateOfBirth)) {
            $extracted['date_of_birth'] = $dateOfBirth;
        }

        if (preg_match('/\b(?:SEX|GENDER)\s*[:\-]?\s*(MALE|FEMALE|M|F)\b/i', $fullText, $matches)) {
            $gender = strtoupper((string) $matches[1]);
            $extracted['gender'] = $gender === 'M' ? 'M' : ($gender === 'F' ? 'F' : $gender);
        } elseif (preg_match('/\b(MALE|FEMALE)\b/i', $fullText, $matches)) {
            $extracted['gender'] = strtoupper($matches[1][0]);
        }

        $address = $this->extractUmidAddressMultiline($normalizedLines);
        if (empty($address)) {
            $address = $this->extractLabeledDriverAddress($normalizedLines);
        }
        if (empty($address)) {
            $address = $this->extractUmidAddressLine($compactText, $normalizedLines);
        }
        if (empty($address)) {
            $address = $this->extractLabeledAddressValue($normalizedLines);
        }
        if (empty($address)) {
            $address = $this->buildAddressFromNationalIdLines($normalizedLines, $compactText);
        }
        if (!empty($address)) {
            $extracted['address'] = $this->trimUmidAddressTrailingFields($address);
        }

        return $extracted;
    }

    protected function trimUmidAddressTrailingFields(string $address): string
    {
        $t = trim($address);

        return trim((string) preg_replace('/\s+(?:DATE\s+OF\s+BIRTH|DATEOFBIRTH|DOB|SEX|GENDER|CRN|COMMON\s+REFERENCE|SURNAME|GIVEN\s+NAME|MIDDLE\s+NAME)\b.*/i', '', $t));
    }

    /**
     * Fix common OCR splits on UMID field labels (old and new card layouts).
     */
    protected function normalizeUmidLabelTypos(string $line): string
    {
        $t = strtoupper(trim($line));
        $replacements = [
            '/\bGIV\s+N\s*A\s*M\s*E\b/' => 'GIVEN NAME',
            '/\bGIVEN\s+N\s*A\s*M\s*E\b/' => 'GIVEN NAME',
            '/\bSUR\s+N\s*A\s*M\s*E\b/' => 'SURNAME',
            '/\bSURN\s*A\s*M\s*E\b/' => 'SURNAME',
            '/\bMID\s*D\s*L\s*E\s+N\s*A\s*M\s*E\b/' => 'MIDDLE NAME',
            '/\bADD\s*R\s*E\s*S\s*S\b/' => 'ADDRESS',
        ];

        foreach ($replacements as $pattern => $replacement) {
            $t = preg_replace($pattern, $replacement, $t);
        }

        return $t;
    }

    /**
     * Newer UMIDs often split address across lines (e.g. PRK/BRGY line, "BATANGAS PHL", then ZIP only).
     */
    protected function extractUmidAddressMultiline(array $lines): string
    {
        $addressParts = [];

        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];
            if (!preg_match('/\bADDRESS\b/i', $line)) {
                continue;
            }

            $sameLine = preg_replace('/^.*?\bADDRESS\s*[:\-]?\s*/i', '', $line);
            $sameLine = $this->trimUmidAddressTrailingFields((string) $sameLine);
            $sameLine = $this->cleanAddressCandidate((string) $sameLine);
            if (!empty($sameLine)) {
                $addressParts[] = $sameLine;
            }

            for ($j = $i + 1; $j <= min($i + 6, count($lines) - 1); $j++) {
                if (preg_match('/\b(DATE\s+OF\s+BIRTH|BIRTH|SEX|GENDER|CRN|COMMON\s+REFERENCE|SURNAME|GIVEN\s+NAME|MIDDLE\s+NAME|MGA\s+PANGALAN|APELYIDO|GITNANG)\b/i', $lines[$j])) {
                    break;
                }

                $c = $this->cleanUmidAddressContinuationLine((string) $lines[$j]);
                if ($c !== '') {
                    $addressParts[] = $c;
                }

                $joined = implode(' ', $addressParts);
                if (preg_match('/\bPHL\s*\d{4}\b/', $joined)) {
                    break;
                }
            }

            if (!empty($addressParts)) {
                return implode(', ', array_values(array_unique($addressParts)));
            }
        }

        return '';
    }

    protected function cleanUmidAddressContinuationLine(string $line): string
    {
        $line = strtoupper(trim($line));
        if ($line === '') {
            return '';
        }

        if (preg_match('/^\d{4}$/', $line)) {
            return $line;
        }

        return $this->cleanAddressCandidate($line);
    }

    /**
     * Single-line OCR blobs (common from cameras): all labels and values in one string.
     *
     * @return array{last_name: string, first_name: string, middle_name: string}
     */
    protected function extractUmidNamesFromCompact(string $compactText): array
    {
        $out = ['last_name' => '', 'first_name' => '', 'middle_name' => ''];
        $t = strtoupper(preg_replace('/\s+/', ' ', trim($compactText)));
        $stop = '(?=\s+(?:ADDRESS|TIRAHAN|DATE\s+OF\s+BIRTH|DATEOFBIRTH|DOB|SEX|GENDER|CRN|COMMON\s+REFERENCE)\b|\s*$)';
        $tok = '([A-Z]{2,25}(?:\s+[A-Z]{2,12})?)';

        if (preg_match('/\bSURNAME\s*[:\-]?\s*' . $tok . '\s+GIVEN\s+NAME\s*[:\-]?\s*' . $tok . '\s+MIDDLE\s+NAME\s*[:\-]?\s*' . $tok . $stop . '/i', $t, $m)) {
            $out['last_name'] = trim(preg_replace('/\s+/', ' ', (string) $m[1]));
            $out['first_name'] = trim(preg_replace('/\s+/', ' ', (string) $m[2]));
            $out['middle_name'] = trim(preg_replace('/\s+/', ' ', (string) $m[3]));

            return $out;
        }

        if (preg_match('/\bSURNAME\s*[:\-]?\s*([A-Z]{2,25})\s+GIVEN\s+NAME\s*[:\-]?\s*([A-Z]{2,25})\b/i', $t, $m)) {
            $out['last_name'] = trim((string) $m[1]);
            $out['first_name'] = trim((string) $m[2]);
        }

        return $out;
    }

    /**
     * UMID permanent address is often one line ending with "PHL" and a 4-digit postal code.
     */
    protected function extractUmidAddressLine(string $compactText, array $lines): string
    {
        // Newer layout: house + optional PRK/PUROK + BRGY. (dot) + ... + PHL + ZIP (one compact string).
        if (preg_match('/\b(\d{1,5}(?:\s+(?:PRK|PUROK)\s*\d+)?\s+BRGY\.?\s+.+?\bPHL\s*\d{4})\b/i', $compactText, $m)) {
            return $this->cleanAddressCandidate(trim((string) $m[1]));
        }

        // Prefer barangay-style lines so a trailing "/23" from a DOB line is not read as a house number.
        if (preg_match('/\b(\d{1,5}\s+BRGY\.?\s+.+?\bPHL\s*\d{4})\b/i', $compactText, $m)) {
            return $this->cleanAddressCandidate(trim((string) $m[1]));
        }

        if (preg_match('/\b(\d{3,5}\s+[A-Z0-9\s.]+?\bPHL\s*\d{4})\b/i', $compactText, $m)) {
            return $this->cleanAddressCandidate(trim((string) $m[1]));
        }

        $merged = '';
        foreach ($lines as $idx => $line) {
            if (preg_match('/\bPHL\b/', $line) && !preg_match('/\b\d{4}\b/', $line) && isset($lines[$idx + 1]) && preg_match('/^\d{4}$/', trim((string) $lines[$idx + 1]))) {
                $merged = $this->cleanAddressCandidate(trim($line . ' ' . trim((string) $lines[$idx + 1])));
                if (!empty($merged)) {
                    return $merged;
                }
            }
        }

        foreach ($lines as $line) {
            if (preg_match('/\bPHL\s*\d{4}\b/', $line) && preg_match('/\d/', $line)) {
                $candidate = $this->cleanAddressCandidate($line);
                if (!empty($candidate)) {
                    return $candidate;
                }
            }
        }

        return '';
    }

    protected function textLooksLikeUmid(string $fullTextUpper): bool
    {
        if (preg_match('/\bUMID\b/', $fullTextUpper)) {
            return true;
        }

        if (preg_match('/UNIFIED\s+MULTI[\s\-]*PURPOSE/', $fullTextUpper)) {
            return true;
        }

        if (preg_match('/\bCOMMON\s+REFERENCE(?:\s+NUMBER)?\b/', $fullTextUpper)) {
            return true;
        }

        if (preg_match('/\bCRN\b/', $fullTextUpper) && preg_match('/\b\d{4}[\-\s]?\d{7}[\-\s]?\d\b/', $fullTextUpper)) {
            return true;
        }

        if (preg_match('/\b\d{4}[\-\s]?\d{7}[\-\s]?\d\b/', $fullTextUpper)
            && preg_match('/\b(SSS|GSIS|PHILHEALTH|PAG[\s\-]*IBIG|PHIL[\s\-]*HEALTH)\b/', $fullTextUpper)) {
            return true;
        }

        return false;
    }

    /**
     * COMELEC Voter's Identification Card: VIN, precinct, surname / first / middle, long-form DOB.
     */
    protected function extractVotersIdData(array $lines, array $extracted): array
    {
        $normalizedLines = array_values(array_filter(array_map(fn ($line) => $this->normalizeOcrLine($line), $lines)));
        $fullText = implode(' ', $normalizedLines);
        $compactText = preg_replace('/\s+/', ' ', $fullText);
        $compactNormalized = $this->normalizeVotersLabelTypos($compactText);

        $lastName = '';
        $firstName = '';
        $middleName = '';

        $mergeVoterNames = function (array $src) use (&$lastName, &$firstName, &$middleName): void {
            if ($lastName === '' && ($src['last_name'] ?? '') !== '') {
                $lastName = (string) $src['last_name'];
            }
            if ($firstName === '' && ($src['first_name'] ?? '') !== '') {
                $firstName = (string) $src['first_name'];
            }
            if ($middleName === '' && ($src['middle_name'] ?? '') !== '') {
                $middleName = (string) $src['middle_name'];
            }
        };

        // Prefer voter-specific parsers first — driver-style label scan can grab wrong tokens from long OCR lines.
        $mergeVoterNames($this->extractVotersNamesFromCompactText($compactNormalized));
        $mergeVoterNames($this->extractVotersNamesBetweenVinAndDob($compactNormalized));
        // Many COMELEC cards print surname / first / middle as three unlabeled lines after the VIN.
        $mergeVoterNames($this->extractVotersNamesUnlabeledTripletAfterVin($compactNormalized));
        $mergeVoterNames($this->extractVotersNamesUnlabeledTripletFromLines($normalizedLines));
        $mergeVoterNames($this->extractVotersNamesFromLineBlocks($normalizedLines));

        if ($lastName === '' || $firstName === '') {
            $commaNames = $this->extractDriverNamesFromCommaLine($normalizedLines);
            if ($lastName === '' && !empty($commaNames['last_name'])) {
                $lastName = $commaNames['last_name'];
            }
            if ($firstName === '' && !empty($commaNames['first_name'])) {
                $firstName = $commaNames['first_name'];
            }
            if ($middleName === '' && !empty($commaNames['middle_name'])) {
                $middleName = $commaNames['middle_name'];
            }
        }

        if ($lastName === '') {
            $lastName = $this->extractLabeledDriverNameValue($normalizedLines, ['SURNAME', 'LAST NAME', 'APELYIDO']);
        }
        if ($firstName === '') {
            $firstName = $this->extractLabeledDriverNameValue($normalizedLines, ['FIRST NAME', 'GIVEN NAME', 'MGA PANGALAN'], true);
        }
        if ($middleName === '') {
            $middleName = $this->extractLabeledDriverNameValue($normalizedLines, ['MIDDLE NAME', 'MIDDLE INITIAL', 'GITNANG APELYIDO'], true);
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
        }

        $vin = $this->extractVotersVin($compactText, $normalizedLines);
        if (!empty($vin)) {
            $extracted['document_number'] = $vin;
        }

        $dateOfBirth = $this->extractLabeledDriverDate($normalizedLines, ['DATE OF BIRTH', 'BIRTH DATE', 'BIRTHDATE', 'DOB', 'PETSA NG KAPANGANAKAN']);
        if (empty($dateOfBirth)) {
            $dateOfBirth = $this->extractDateOfBirthWithEnglishMonth($fullText);
        }
        if (empty($dateOfBirth) && preg_match('/\b(\d{2}[\/\-]\d{2}[\/\-]\d{4}|\d{4}[\/\-]\d{2}[\/\-]\d{2})\b/', $fullText, $matches)) {
            $dateOfBirth = $this->normalizeDate((string) $matches[1]);
        }
        if (!empty($dateOfBirth)) {
            $extracted['date_of_birth'] = $dateOfBirth;
        }

        if (preg_match('/\b(?:SEX|GENDER)\s*[:\-]?\s*(MALE|FEMALE|M|F)\b/i', $fullText, $matches)) {
            $gender = strtoupper((string) $matches[1]);
            $extracted['gender'] = $gender === 'M' ? 'M' : ($gender === 'F' ? 'F' : $gender);
        } elseif (preg_match('/\b(MALE|FEMALE)\b/i', $fullText, $matches)) {
            $extracted['gender'] = strtoupper($matches[1][0]);
        }

        if (preg_match('/\bCITIZENSHIP\s*[:\-]?\s*([A-Z]{3,20})\b/i', $fullText, $m)) {
            $extracted['nationality'] = trim((string) $m[1]);
        }

        $address = $this->extractVotersAddressAfterLabel($normalizedLines, $compactText);
        if (empty($address)) {
            $address = $this->extractLabeledDriverAddress($normalizedLines);
        }
        if (empty($address)) {
            $address = $this->extractLabeledAddressValue($normalizedLines);
        }
        if (empty($address)) {
            $address = $this->buildAddressFromNationalIdLines($normalizedLines, $compactText);
        }
        if (!empty($address)) {
            $extracted['address'] = $this->normalizeComelecAddressForParseAddress($address, $compactText);
        }

        return $extracted;
    }

    /**
     * Fix spaced / garbled COMELEC field labels before regex extraction.
     */
    protected function normalizeVotersLabelTypos(string $text): string
    {
        $t = strtoupper(preg_replace('/\s+/', ' ', trim($text)));

        $replacements = [
            '/\bSUR\s+N\s*A\s*M\s*E\b/' => 'SURNAME',
            '/\bSURN\s*A\s*M\s*E\b/' => 'SURNAME',
            '/\bSUR\s*N\s*A\s*M\s*E\b/' => 'SURNAME',
            '/\bFIR\s*ST\s+N\s*A\s*M\s*E\b/' => 'FIRST NAME',
            '/\bF\s*I\s*R\s*S\s*T\s+N\s*A\s*M\s*E\b/' => 'FIRST NAME',
            '/\bF\s*I\s*R\s*S\s*T\s*NAME\b/' => 'FIRST NAME',
            '/\bMID\s*D\s*L\s*E\s+N\s*A\s*M\s*E\b/' => 'MIDDLE NAME',
            '/\bM\s*I\s*D\s*D\s*L\s*E\s+N\s*A\s*M\s*E\b/' => 'MIDDLE NAME',
            '/\bMID\s*DLE\s+NAME\b/' => 'MIDDLE NAME',
        ];

        foreach ($replacements as $pattern => $replacement) {
            $t = preg_replace($pattern, $replacement, $t);
        }

        return $t;
    }

    /**
     * Names usually sit between the VIN token and "DATE OF BIRTH" on COMELEC cards — isolate that span for parsing.
     */
    protected function extractVotersNamesBetweenVinAndDob(string $compactText): array
    {
        $t = strtoupper(preg_replace('/\s+/', ' ', trim($compactText)));
        if (!preg_match('/\b(\d{4}-[A-Z0-9]+-[A-Z0-9]{6,}-\d)\b/', $t, $vm)) {
            return ['last_name' => '', 'first_name' => '', 'middle_name' => ''];
        }

        $vinToken = strtoupper((string) $vm[1]);
        $pos = strpos($t, $vinToken);
        if ($pos === false) {
            return ['last_name' => '', 'first_name' => '', 'middle_name' => ''];
        }

        $afterVin = trim(substr($t, $pos + strlen($vinToken)));
        if ($afterVin === '') {
            return ['last_name' => '', 'first_name' => '', 'middle_name' => ''];
        }

        if (preg_match('/\bDATE\s+OF\s+BIRTH\b/i', $afterVin, $dm, PREG_OFFSET_CAPTURE)) {
            $segment = trim(substr($afterVin, 0, (int) $dm[0][1]));

            return $this->extractVotersNamesFromCompactText($this->normalizeVotersLabelTypos($segment));
        }

        return $this->extractVotersNamesFromCompactText($this->normalizeVotersLabelTypos($afterVin));
    }

    /**
     * Strip header fragments that sometimes sit between VIN and the three name lines on COMELEC IDs.
     */
    protected function stripComelecHeaderPrefixBeforeNames(string $segment): string
    {
        $s = strtoupper(preg_replace('/\s+/', ' ', trim($segment)));

        for ($i = 0; $i < 30; $i++) {
            $before = $s;
            $s = preg_replace('/^[\s,;:]+/', '', $s);
            $s = preg_replace('/^(?:(?:LIPA\s+CITY,?\s*)|(?:BATANGAS)\s+|(?:COMMISSION\s+ON\s+ELECTIONS)\s+|(?:REPUBLIC\s+OF\s+THE\s+PHILIPPINES)\s+|(?:REPUBLIC\s+OF\s+PHILIPPINES)\s+|(?:COMELEC)\s+|(?:PHILIPPINES)\s+|(?:ELECTIONS)\s+|(?:COMMISSION)\s+|(?:REPUBLIC)\s+|(?:THE)\s+|(?:OF)\s+|(?:ON)\s+)+/i', '', $s);
            if ($s === $before) {
                break;
            }
        }

        return trim($s);
    }

    /**
     * Three all-caps tokens after VIN, before DOB / civil / address (no SURNAME/FIRST NAME labels on card).
     */
    protected function extractVotersNamesUnlabeledTripletAfterVin(string $compactText): array
    {
        $out = ['last_name' => '', 'first_name' => '', 'middle_name' => ''];
        $t = strtoupper(preg_replace('/\s+/', ' ', trim($compactText)));

        if (!preg_match('/\b(\d{4}-[A-Z0-9]+-[A-Z0-9]{6,}-\d)\b(.*)$/is', $t, $vm)) {
            return $out;
        }

        $afterVin = trim((string) $vm[2]);
        if ($afterVin === '') {
            return $out;
        }

        if (preg_match('/\bDATE\s+OF\s+BIRTH\b/i', $afterVin, $dm, PREG_OFFSET_CAPTURE)) {
            $segment = trim(substr($afterVin, 0, (int) $dm[0][1]));
        } else {
            $segment = $afterVin;
        }

        $seg = $this->stripComelecHeaderPrefixBeforeNames($segment);
        if ($seg === '') {
            return $out;
        }

        // Segment is usually cut *before* "DATE OF BIRTH", so allow end-of-string as well as a following field.
        $dobStop = '(?=\s+(?:DATE\s+OF\s+BIRTH|DATEOFBIRTH|DOB|OCTOBER|JANUARY|FEBRUARY|MARCH|APRIL|MAY|JUNE|JULY|AUGUST|SEPTEMBER|NOVEMBER|DECEMBER|CIVIL\s+STATUS|CIVIL|ADDRESS|PRECINCT|CITIZENSHIP|MARRIED|SINGLE|FILIPINO|WIDOW)\b|\s*$)';

        if (!preg_match('/^([A-Z0-9]{2,25})\s+([A-Z0-9]{2,25})\s+([A-Z0-9]{2,25})\s*' . $dobStop . '/is', $seg, $m)) {
            return $out;
        }

        $ln = $this->sanitizeVotersNameToken($this->fixVoterNameOcrDigitsInToken((string) $m[1]), false);
        $fn = $this->sanitizeVotersNameToken($this->fixVoterNameOcrDigitsInToken((string) $m[2]), true);
        $mn = $this->sanitizeVotersNameToken($this->fixVoterNameOcrDigitsInToken((string) $m[3]), true);

        if ($ln !== '' && $fn !== '' && $mn !== '') {
            $out['last_name'] = $ln;
            $out['first_name'] = $fn;
            $out['middle_name'] = $mn;
        }

        return $out;
    }

    protected function extractVotersNamesUnlabeledTripletFromLines(array $lines): array
    {
        $out = ['last_name' => '', 'first_name' => '', 'middle_name' => ''];
        $vinIdx = -1;

        foreach ($lines as $i => $line) {
            if (preg_match('/\b\d{4}-[A-Z0-9]+-[A-Z0-9]{6,}-\d\b/', $line)) {
                $vinIdx = $i;
                break;
            }
        }

        if ($vinIdx < 0) {
            return $out;
        }

        $buf = [];
        for ($j = $vinIdx + 1; $j < count($lines); $j++) {
            $line = trim((string) $lines[$j]);
            if ($line === '') {
                continue;
            }

            if ($this->isVotersNameSectionStopLine($line)) {
                break;
            }

            if ($this->isVotersNameSkippableHeaderLine($line)) {
                continue;
            }

            $tok = $this->sanitizeVotersNameToken($this->fixVoterNameOcrDigitsInToken($line), false);
            if ($tok === '' || str_contains($tok, ' ')) {
                continue;
            }

            $buf[] = $tok;
            if (count($buf) >= 3) {
                $out['last_name'] = $buf[0];
                $out['first_name'] = $buf[1];
                $out['middle_name'] = $buf[2];

                break;
            }
        }

        return $out;
    }

    protected function isVotersNameSectionStopLine(string $line): bool
    {
        return (bool) preg_match('/\b(DATE\s+OF\s+BIRTH|DATEOFBIRTH|DOB|CIVIL\s+STATUS|ADDRESS|PRECINCT|CITIZENSHIP|MARRIED|SINGLE|FILIPINO|WIDOW|WIDOWED|DIVORCED|OCTOBER|JANUARY|FEBRUARY|MARCH|APRIL|MAY|JUNE|JULY|AUGUST|SEPTEMBER|NOVEMBER|DECEMBER)\b/i', $line);
    }

    protected function isVotersNameSkippableHeaderLine(string $line): bool
    {
        if (preg_match('/^LIPA\s+CITY,?\s*BATANGAS$/i', $line)) {
            return true;
        }

        return (bool) preg_match('/^(?:LIPA\s+CITY,?|BATANGAS|COMMISSION\s+ON\s+ELECTIONS|REPUBLIC\s+OF\s+THE\s+PHILIPPINES|REPUBLIC\s+OF\s+PHILIPPINES|COMELEC|PHILIPPINES|REPUBLIC|COMMISSION|ELECTIONS|THE|OF|ON|AND)$/i', $line);
    }

    /**
     * Map common OCR digit misreads inside name tokens (e.g. H3RNANDEZ).
     */
    protected function fixVoterNameOcrDigitsInToken(string $token): string
    {
        $token = strtoupper(trim($token));
        if ($token === '' || !preg_match('/\d/', $token)) {
            return $token;
        }

        static $digitToLetter = [
            '0' => 'O', '1' => 'I', '2' => 'Z', '3' => 'E', '4' => 'A',
            '5' => 'S', '6' => 'G', '7' => 'T', '8' => 'B', '9' => 'G',
        ];

        $out = '';
        for ($i = 0, $len = strlen($token); $i < $len; $i++) {
            $c = $token[$i];
            $out .= $digitToLetter[$c] ?? $c;
        }

        return $out;
    }

    /**
     * COMELEC OCR often yields one long line; driver-style extraction requires the value at EOL.
     */
    protected function extractVotersNamesFromCompactText(string $compactText): array
    {
        $out = ['last_name' => '', 'first_name' => '', 'middle_name' => ''];
        $t = strtoupper(preg_replace('/\s+/', ' ', trim($compactText)));
        $t = $this->normalizeVotersLabelTypos($t);

        $nameTok = '([A-Z0-9]{2,25})';
        $middleTok = '([A-Z0-9]{2,25}(?:\s+[A-Z0-9]{2,12})?)';
        $stop = '(?=\s+(?:DATE\s+OF\s+BIRTH|DATEOFBIRTH|DOB|ADDRESS|PRECINCT|CIVIL\s+STATUS|CITIZENSHIP)\b|\s*$)';

        $patterns = [
            '/\bSURNAME\s*[:\-]?\s*' . $nameTok . '\s+FIRST\s+NAME\s*[:\-]?\s*' . $nameTok . '\s+MIDDLE\s+NAME\s*[:\-]?\s*' . $middleTok . $stop . '/i',
            '/\bSURNAME\s*[:\-]?\s*' . $nameTok . '\s*[,;]\s*' . $nameTok . '\s*[,;]\s*' . $middleTok . $stop . '/i',
            '/\bSURNAME\s*[:\-]?\s*' . $nameTok . '\s+FIRST\s+NAME\s*[:\-]?\s*' . $nameTok . '\b/i',
            '/\bSURNAME\s*[:\-]?\s*' . $nameTok . '\s+' . $nameTok . '\s+' . $nameTok . $stop . '/i',
        ];

        foreach ($patterns as $pattern) {
            if (!preg_match($pattern, $t, $m)) {
                continue;
            }
            $ln = $this->sanitizeVotersNameToken($this->fixVoterNameOcrDigitsInToken((string) $m[1]), false);
            $fn = $this->sanitizeVotersNameToken($this->fixVoterNameOcrDigitsInToken((string) $m[2]), true);
            $mn = isset($m[3]) ? $this->sanitizeVotersNameToken($this->fixVoterNameOcrDigitsInToken((string) $m[3]), true) : '';

            if ($ln !== '' && $fn !== '') {
                $out['last_name'] = $ln;
                $out['first_name'] = $fn;
                $out['middle_name'] = $mn;

                break;
            }
        }

        return $out;
    }

    /**
     * Read name values on the lines after SURNAME / FIRST NAME / MIDDLE NAME labels (column layout).
     */
    protected function extractVotersNamesFromLineBlocks(array $lines): array
    {
        $out = ['last_name' => '', 'first_name' => '', 'middle_name' => ''];
        $map = [
            'last_name' => ['SURNAME', 'LAST NAME'],
            'first_name' => ['FIRST NAME', 'GIVEN NAME'],
            'middle_name' => ['MIDDLE NAME', 'MIDDLE INITIAL'],
        ];

        foreach ($map as $field => $labels) {
            if ($out[$field] !== '') {
                continue;
            }

            foreach ($lines as $i => $line) {
                $matchedLabel = '';
                foreach ($labels as $lab) {
                    $uLab = strtoupper($lab);
                    if (!str_contains($line, $uLab)) {
                        continue;
                    }
                    $matchedLabel = $uLab;
                    break;
                }
                if ($matchedLabel === '') {
                    continue;
                }

                if (preg_match('/' . preg_quote($matchedLabel, '/') . '\s*[:\-]?\s*([A-Z0-9]{2,25})\s*$/i', $line, $m)) {
                    $tok = $this->sanitizeVotersNameToken($this->fixVoterNameOcrDigitsInToken((string) $m[1]), $field !== 'last_name');
                    if ($tok !== '') {
                        $out[$field] = $tok;
                        break;
                    }
                }

                for ($j = $i + 1; $j <= min($i + 8, count($lines) - 1); $j++) {
                    $ln = trim((string) $lines[$j]);
                    if ($ln === '') {
                        continue;
                    }
                    if (preg_match('/\b(SURNAME|FIRST\s+NAME|MIDDLE\s+NAME|DATE\s+OF\s+BIRTH|DATEOFBIRTH|DOB|PRECINCT|VIN|ADDRESS|CIVIL\s+STATUS|CITIZENSHIP|COMMISSION|COMELEC|REPUBLIC|CHAIRMAN|VOTER)\b/i', $ln)) {
                        break;
                    }
                    $tok = $this->sanitizeVotersNameToken($this->fixVoterNameOcrDigitsInToken($ln), $field !== 'last_name');
                    if ($tok !== '') {
                        $out[$field] = $tok;
                        break;
                    }
                }
            }
        }

        return $out;
    }

    protected function sanitizeVotersNameToken(string $raw, bool $allowMultiWord): string
    {
        $raw = strtoupper(preg_replace('/\s+/', ' ', trim($raw)));
        $raw = trim((string) $raw, " \t\n\r\0\x0B,:;.-");

        if ($raw === '' || strlen($raw) > 45 || preg_match('/\d/', $raw)) {
            return '';
        }

        if (!preg_match('/^[A-Z\s]+$/', $raw)) {
            return '';
        }

        $badExact = [
            'SURNAME', 'FIRST', 'NAME', 'MIDDLE', 'LAST', 'GIVEN', 'DATE', 'BIRTH', 'VIN', 'PRECINCT',
            'ADDRESS', 'CIVIL', 'STATUS', 'CITIZENSHIP', 'MARRIED', 'SINGLE', 'WIDOW', 'WIDOWED', 'FILIPINO',
            'COMELEC', 'REPUBLIC', 'COMMISSION', 'ELECTIONS', 'CHAIRMAN', 'VOTER', 'IDENTIFICATION',
            'SIGNATURE', 'RIGHT', 'THUMB', 'BATANGAS', 'LIPA', 'CITY', 'MALITLIT', 'PROVINCE', 'REGION',
            'JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER',
        ];
        foreach (explode(' ', $raw) as $w) {
            if ($w !== '' && in_array($w, $badExact, true)) {
                return '';
            }
        }

        $wc = count(array_filter(explode(' ', $raw)));
        if (!$allowMultiWord && $wc > 2) {
            return '';
        }
        if ($allowMultiWord && $wc > 3) {
            return '';
        }

        $vowels = preg_match_all('/[AEIOUY]/', $raw);
        if ($vowels < 1) {
            return '';
        }

        return $raw;
    }

    /**
     * Turn COMELEC "MALITLIT LIPA CITY [, MALITLIT]" into a string parseAddress maps to barangay + city.
     */
    protected function normalizeComelecAddressForParseAddress(string $address, string $compactText): string
    {
        $u = strtoupper(preg_replace('/\s+/', ' ', trim($address)));
        if ($u === '') {
            return '';
        }

        if (preg_match('/\bBRGY\./i', $u)) {
            if (preg_match('/\bBATANGAS\b/', strtoupper($compactText)) && !preg_match('/\bBATANGAS\b/', $u)) {
                return $u . ', BATANGAS';
            }

            return $address;
        }

        if (preg_match('/^([A-Z]{3,25})\s+(.+?\bCITY)\s*,?\s*\1$/', $u, $m)) {
            $u = $m[1] . ' ' . $m[2];
        } elseif (preg_match('/^([A-Z]{3,25})\s+(.+?\bCITY)\s+\1$/', $u, $m)) {
            $u = $m[1] . ' ' . $m[2];
        }

        if (preg_match('/^([A-Z]+(?:\s+[A-Z]+){0,1})\s+((?:[A-Z]+\s+){1,3}CITY)$/i', $u, $m)) {
            $brgy = trim((string) $m[1]);
            $cityLine = trim((string) $m[2]);
            if (strlen($brgy) >= 3 && strlen($cityLine) >= 6) {
                $prov = preg_match('/\bBATANGAS\b/', strtoupper($compactText)) ? ', BATANGAS' : '';

                return 'BRGY. ' . $brgy . ', ' . $cityLine . $prov;
            }
        }

        return $address;
    }

    /**
     * VIN on voter's ID: e.g. 1014-0349A-J2363JVH20000-1 (segments separated by hyphens).
     */
    protected function extractVotersVin(string $compactText, array $lines): string
    {
        if (preg_match('/\b(?:VIN|VOTER\s+IDENTIFICATION(?:\s+NUMBER)?)\s*[:\#\-]?\s*([A-Z0-9][A-Z0-9\-]{12,45})\b/i', $compactText, $m)) {
            $candidate = strtoupper(preg_replace('/\s+/', '', (string) $m[1]));
            if ($this->looksLikeVotersVin($candidate)) {
                return $candidate;
            }
        }

        foreach ($lines as $line) {
            if (!preg_match('/\bVIN\b/i', $line)) {
                continue;
            }
            if (preg_match('/\bVIN\s*[:\#\-]?\s*([A-Z0-9][A-Z0-9\-]{12,45})\b/i', $line, $m)) {
                $candidate = strtoupper(preg_replace('/\s+/', '', (string) $m[1]));
                if ($this->looksLikeVotersVin($candidate)) {
                    return $candidate;
                }
            }
        }

        if (preg_match('/\b(\d{4}-[A-Z0-9]+-[A-Z0-9]{6,}-\d)\b/i', $compactText, $m)
            && $this->textLooksLikeVotersId(strtoupper($compactText))) {
            $candidate = strtoupper((string) $m[1]);

            return $this->looksLikeVotersVin($candidate) ? $candidate : '';
        }

        return '';
    }

    protected function looksLikeVotersVin(string $value): bool
    {
        $value = strtoupper(trim($value));
        if (strlen($value) < 18 || strlen($value) > 48) {
            return false;
        }

        if (substr_count($value, '-') < 3) {
            return false;
        }

        return (bool) preg_match('/^\d{4}-/', $value);
    }

    /**
     * COMELEC cards often use "October 23, 1963" (comma optional).
     */
    protected function extractDateOfBirthWithEnglishMonth(string $text): string
    {
        if (preg_match('/\b(JANUARY|FEBRUARY|MARCH|APRIL|MAY|JUNE|JULY|AUGUST|SEPTEMBER|OCTOBER|NOVEMBER|DECEMBER)\s+(\d{1,2}),?\s*(\d{4})\b/i', $text, $m)) {
            return $this->englishMonthDayYearToIso((string) $m[1], (string) $m[2], (string) $m[3]);
        }

        return '';
    }

    protected function englishMonthDayYearToIso(string $monthName, string $day, string $year): string
    {
        $map = [
            'january' => '01', 'february' => '02', 'march' => '03', 'april' => '04',
            'may' => '05', 'june' => '06', 'july' => '07', 'august' => '08',
            'september' => '09', 'october' => '10', 'november' => '11', 'december' => '12',
        ];
        $key = strtolower(trim($monthName));
        $mm = $map[$key] ?? '';
        if ($mm === '') {
            return '';
        }
        $d = str_pad(preg_replace('/\D/', '', $day), 2, '0', STR_PAD_LEFT);
        $y = preg_replace('/\D/', '', $year);
        if (strlen($y) !== 4 || (int) $d < 1 || (int) $d > 31) {
            return '';
        }

        return "{$y}-{$mm}-{$d}";
    }

    protected function extractVotersAddressAfterLabel(array $lines, string $compactText): string
    {
        if (preg_match('/\bADDRESS\s*[:\-]?\s*([A-Z0-9\s,.]+?)(?=\s+\bPRECINCT\b|\s+\bVIN\b|\s+\bDATE OF BIRTH\b|\s+\bCIVIL STATUS\b|\s+\bCITIZENSHIP\b|\s+\bSURNAME\b|$)/i', $compactText, $m)) {
            $tail = trim((string) $m[1]);
            $tail = preg_replace('/\b(PRECINCT|VIN|DATE OF BIRTH|CIVIL STATUS|CITIZENSHIP|SURNAME|FIRST NAME|MIDDLE NAME)\b.*$/i', '', $tail);

            return $this->cleanAddressCandidate(trim((string) $tail));
        }

        foreach ($lines as $i => $line) {
            if (!preg_match('/\bADDRESS\b/i', $line)) {
                continue;
            }
            $same = preg_replace('/^.*?\bADDRESS\s*[:\-]?\s*/i', '', $line);
            $same = $this->cleanAddressCandidate(trim((string) $same));
            $parts = [];
            if (!empty($same)) {
                $parts[] = $same;
            }
            for ($j = $i + 1; $j <= min($i + 5, count($lines) - 1); $j++) {
                if (preg_match('/\b(PRECINCT|VIN|DATE OF BIRTH|CIVIL STATUS|CITIZENSHIP|SURNAME|FIRST NAME|MIDDLE NAME)\b/i', $lines[$j])) {
                    break;
                }
                $candidate = $this->cleanAddressCandidate((string) $lines[$j]);
                if (!empty($candidate)) {
                    $parts[] = $candidate;
                }
            }
            if (!empty($parts)) {
                return implode(', ', array_values(array_unique($parts)));
            }
        }

        return '';
    }

    protected function textLooksLikeVotersId(string $fullTextUpper): bool
    {
        if (preg_match('/\bCOMELEC\b/', $fullTextUpper)) {
            return true;
        }

        if (preg_match('/COMMISSION\s+ON\s+ELECTIONS/', $fullTextUpper)) {
            return true;
        }

        if (preg_match('/\bVOTER[\'S]*\s+IDENTIFICATION\b/', $fullTextUpper)) {
            return true;
        }

        if (preg_match('/\bVOTERS?\s+ID\b/', $fullTextUpper)) {
            return true;
        }

        if (preg_match('/\bVIN\b/', $fullTextUpper) && preg_match('/\bPRECINCT\b/', $fullTextUpper)) {
            return true;
        }

        return false;
    }

    protected function textLooksLikeSeniorCitizenId(string $fullTextUpper): bool
    {
        if (preg_match('/\bOSCA\b/', $fullTextUpper)) {
            return true;
        }

        if (preg_match('/\bSENIOR\s+CITIZEN/', $fullTextUpper)) {
            return true;
        }

        if (preg_match('/OFFICE\s+FOR\s+SENIOR\s+CITIZENS/', $fullTextUpper)) {
            return true;
        }

        if (preg_match('/SENIOR\s+CITIZENS\s+AFFAIRS/', $fullTextUpper)) {
            return true;
        }

        if (preg_match('/\bAFFAIRS\s+OSCA\b/', $fullTextUpper)) {
            return true;
        }

        if (preg_match('/NON[\s\-]*TRANSFERABLE.*VALID\s+ANYWHERE/i', $fullTextUpper)
            && preg_match('/\bDATE\s+OF\s+BIRTH\b/', $fullTextUpper)) {
            return true;
        }

        return false;
    }

    /**
     * City / municipal Senior Citizen ID (e.g. Lipa OSCA): name above NAME line, comma address, ID no. like 131-58599.
     */
    protected function extractSeniorCitizenIdData(array $lines, array $extracted): array
    {
        $normalizedLines = array_values(array_filter(array_map(fn ($line) => $this->normalizeOcrLine($line), $lines)));
        $fullText = implode(' ', $normalizedLines);
        $compact = preg_replace('/\s+/', ' ', $fullText);

        $names = $this->extractSeniorCitizenNames($compact, $normalizedLines);
        if (!empty($names['last_name'])) {
            $extracted['last_name'] = $names['last_name'];
        }
        if (!empty($names['first_name'])) {
            $extracted['first_name'] = $names['first_name'];
        }
        if (!empty($names['middle_name'])) {
            $extracted['middle_name'] = $names['middle_name'];
        }

        if (!empty($extracted['last_name']) && !empty($extracted['first_name'])) {
            $extracted['full_name'] = trim(
                $extracted['last_name']
                . ', '
                . $extracted['first_name']
                . (!empty($extracted['middle_name']) ? ' ' . $extracted['middle_name'] : '')
            );
        }

        if (preg_match('/\b(\d{2,4}-\d{4,7})\b/', $compact, $m)) {
            $extracted['document_number'] = trim((string) $m[1]);
        }

        $dob = $this->extractLabeledDriverDate($normalizedLines, ['DATE OF BIRTH', 'BIRTH DATE', 'DATE OF BIRTH / AGE', 'DOB']);
        if (empty($dob) && preg_match('/\b(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4})\s*(?:\/\s*\d{1,3}\b|\b)/', $compact, $dm)) {
            $dob = $this->normalizeDate((string) $dm[1]);
        }
        if (empty($dob)) {
            $dob = $this->extractDateOfBirthWithEnglishMonth($fullText);
        }
        if (!empty($dob)) {
            $extracted['date_of_birth'] = $dob;
        }

        $issue = $this->extractLabeledDriverDate($normalizedLines, ['DATE ISSUE', 'ISSUE DATE', 'DATE ISSUED']);
        if (!empty($issue)) {
            $extracted['expiration_date'] = $issue;
        }

        $address = '';
        if (preg_match('/\bADDRESS\s*[:\-]?\s*([A-Z][A-Z\s,]{3,70}?)(?=\s+(?:DATE\s+OF|DATEOF|DATE\s+ISSUE|DATE\s+ISSUED|PRINTED\s+NAME|PRINTED|CTRL|THIS\s+CARD)\b|\s+\d{1,2}\/\d{1,2}\/\d{4}\b)/i', $compact, $am)) {
            $address = $this->cleanAddressCandidate(trim((string) $am[1]));
        }
        // OSCA layout often prints the value *above* the ADDRESS label (reads earlier in OCR string).
        // Value-above-label: barangay is one (or few) words, comma, then "... CITY" before ADDRESS.
        if ($address === '' && preg_match('/\b([A-Z]{5,22},\s*[A-Z\s]{3,40}\bCITY)\s+ADDRESS\b/i', $compact, $am)) {
            $address = $this->cleanAddressCandidate(trim((string) $am[1]));
        }
        if ($address === '') {
            $address = $this->extractLabeledDriverAddress($normalizedLines);
        }
        if (!empty($address)) {
            if (preg_match('/^\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4}/', $address)) {
                $address = '';
            }
        }
        if (!empty($address)) {
            $extracted['address'] = $this->normalizeSeniorCitizenAddressForParse($address, $compact);
        }

        return $extracted;
    }

    /**
     * @return array{last_name: string, first_name: string, middle_name: string}
     */
    protected function extractSeniorCitizenNames(string $compact, array $lines): array
    {
        $out = ['last_name' => '', 'first_name' => '', 'middle_name' => ''];
        $t = strtoupper(preg_replace('/\s+/', ' ', trim($compact)));

        // Footer: "JOCELYN VILLARUZ HERNANDEZ" before PRINTED NAME / THUMBMARK (First Middle Last).
        if (preg_match('/\b([A-Z]{2,15})\s+([A-Z]{4,20})\s+([A-Z]{2,20})\b(?=\s+(?:PRINTED\s+NAME|PRINTED|THUMBMARK|THUMB\s*MARK|NON[\s\-]*TRANSFERABLE|THIS\s+CARD)\b)/i', $t, $m)) {
            $fn = $this->sanitizeSeniorNameToken((string) $m[1]);
            $mn = $this->sanitizeSeniorNameToken((string) $m[2]);
            $ln = $this->sanitizeSeniorNameToken((string) $m[3]);
            if ($fn !== '' && $mn !== '' && $ln !== '') {
                return ['first_name' => $fn, 'middle_name' => $mn, 'last_name' => $ln];
            }
        }

        // Main field: NAME ... JOCELYN V. HERNANDEZ (First, middle initial, Last).
        if (preg_match('/\bNAME\s*[:\-]?\s*([A-Z]+)\s+([A-Z])\.?\s+([A-Z]{2,20})\b/i', $t, $m)) {
            $fn = $this->sanitizeSeniorNameToken((string) $m[1]);
            $mi = $this->sanitizeSeniorNameToken((string) $m[2]);
            $ln = $this->sanitizeSeniorNameToken((string) $m[3]);
            if ($fn !== '' && $ln !== '') {
                $mn = $mi;
                if (strlen($mi) === 1) {
                    $fullMiddle = $this->inferSeniorMiddleFromCompact($t, $fn, $ln);
                    if ($fullMiddle !== '') {
                        $mn = $fullMiddle;
                    }
                }

                return ['first_name' => $fn, 'middle_name' => $mn, 'last_name' => $ln];
            }
        }

        return $this->extractSeniorCitizenNamesFromLines($lines);
    }

    protected function inferSeniorMiddleFromCompact(string $t, string $firstName, string $lastName): string
    {
        if (preg_match_all('/\b([A-Z]{4,22})\b/', $t, $all)) {
            foreach ($all[1] as $w) {
                if ($w === $firstName || $w === $lastName) {
                    continue;
                }
                if (in_array($w, ['MALITLIT', 'LIPA', 'CITY', 'REPUBLIC', 'PHILIPPINES', 'SENIOR', 'CITIZEN', 'CITIZENS', 'OFFICE', 'AFFAIRS', 'PRINTED', 'THUMBMARK', 'TRANSFERABLE', 'VALID', 'ANYWHERE', 'COUNTRY', 'ISSUE', 'DATE', 'BIRTH', 'AGE', 'CTRL', 'NUMBER', 'CARD', 'THIS', 'NON'], true)) {
                    continue;
                }
                if (str_starts_with($w, 'CITY')) {
                    continue;
                }

                return $w;
            }
        }

        return '';
    }

    protected function sanitizeSeniorNameToken(string $token): string
    {
        $token = strtoupper(preg_replace('/\s+/', '', trim($token)));
        $token = $this->fixVoterNameOcrDigitsInToken($token);

        return preg_match('/^[A-Z]+$/', $token) && strlen($token) >= 1 && strlen($token) <= 25 ? $token : '';
    }

    /**
     * @return array{last_name: string, first_name: string, middle_name: string}
     */
    protected function extractSeniorCitizenNamesFromLines(array $lines): array
    {
        $out = ['last_name' => '', 'first_name' => '', 'middle_name' => ''];
        foreach ($lines as $i => $line) {
            if (!preg_match('/\bNAME\b/', $line)) {
                continue;
            }
            if (preg_match('/\bNAME\s*[:\-]?\s*([A-Z]+)\s+([A-Z])\.?\s+([A-Z]{2,20})\b/i', $line, $m)) {
                $out['first_name'] = $this->sanitizeSeniorNameToken((string) $m[1]);
                $out['middle_name'] = $this->sanitizeSeniorNameToken((string) $m[2]);
                $out['last_name'] = $this->sanitizeSeniorNameToken((string) $m[3]);

                return $out;
            }
            if ($i > 0 && preg_match('/^([A-Z]+)\s+([A-Z])\.?\s+([A-Z]{2,20})$/', trim((string) $lines[$i - 1]), $m)) {
                $out['first_name'] = $this->sanitizeSeniorNameToken((string) $m[1]);
                $out['middle_name'] = $this->sanitizeSeniorNameToken((string) $m[2]);
                $out['last_name'] = $this->sanitizeSeniorNameToken((string) $m[3]);

                return $out;
            }
            if (isset($lines[$i + 1]) && preg_match('/^([A-Z]+)\s+([A-Z])\.?\s+([A-Z]{2,20})$/', trim((string) $lines[$i + 1]), $m)) {
                $out['first_name'] = $this->sanitizeSeniorNameToken((string) $m[1]);
                $out['middle_name'] = $this->sanitizeSeniorNameToken((string) $m[2]);
                $out['last_name'] = $this->sanitizeSeniorNameToken((string) $m[3]);

                return $out;
            }
        }

        return $out;
    }

    protected function normalizeSeniorCitizenAddressForParse(string $address, string $compactText): string
    {
        $u = strtoupper(preg_replace('/\s+/', ' ', trim($address)));
        if ($u === '') {
            return '';
        }

        if (preg_match('/\bBRGY\./i', $u)) {
            return $this->normalizeComelecAddressForParseAddress($address, $compactText);
        }

        if (preg_match('/^([A-Z]+)\s*,\s*(.+?\bCITY)\s*$/i', $u, $m)) {
            return 'BRGY. ' . strtoupper(trim($m[1])) . ', ' . strtoupper(trim($m[2]));
        }

        return $this->normalizeComelecAddressForParseAddress($address, $compactText);
    }

    protected function normalizeIdType(string $idType): string
    {
        $normalized = strtolower(trim($idType));

        return match ($normalized) {
            'passport', 'pass port', 'password' => 'passport',
            'driver', 'driver_license', 'drivers_license', 'driver-license' => 'driver_license',
            'umid' => 'umid',
            'voters_id', 'voters', 'voter_id', 'comelec' => 'voters_id',
            'senior_citizen_id', 'senior', 'senior_id', 'osca', 'sc_id' => 'senior_citizen_id',
            'auto', 'automatic', '' => 'auto',
            default => 'national',
        };
    }

    protected function resolveIdTypeFromText(array $lines, string $requestedType): string
    {
        if ($requestedType === 'passport') {
            return 'passport';
        }

        if ($requestedType === 'driver_license') {
            return 'driver_license';
        }

        if ($requestedType === 'umid') {
            return 'umid';
        }

        if ($requestedType === 'voters_id') {
            return 'voters_id';
        }

        if ($requestedType === 'senior_citizen_id') {
            return 'senior_citizen_id';
        }

        $fullText = strtoupper(implode(' ', $lines));

        if (preg_match('/\bPASSPORT\b/', $fullText)) {
            return 'passport';
        }

        foreach ($lines as $line) {
            $normalized = strtoupper(trim((string) $line));
            if (str_starts_with($normalized, 'P<') || str_contains($normalized, '<<')) {
                return 'passport';
            }
        }

        if (preg_match('/\b(DRIVER|LICENSE|LICENCE|DL)\b/', $fullText)) {
            return 'driver_license';
        }

        if ($this->textLooksLikeVotersId($fullText)) {
            return 'voters_id';
        }

        if ($this->textLooksLikeSeniorCitizenId($fullText)) {
            return 'senior_citizen_id';
        }

        if ($this->textLooksLikeUmid($fullText)) {
            return 'umid';
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
                if (preg_match('/\b(DATE OF BIRTH|BIRTH|EXPIRATION|EXPIRY|SEX|GENDER|NATIONALITY|LICENSE|LICENCE|PRECINCT|VIN|CIVIL STATUS|CITIZENSHIP)\b/', $lines[$j])) {
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
