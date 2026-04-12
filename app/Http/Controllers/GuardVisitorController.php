<?php

namespace App\Http\Controllers;

use App\Services\OCRService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class GuardVisitorController extends Controller
{
    /**
     * Return active offices for guard visitor step.
     */
    public function getOffices()
    {
        try {
            $offices = DB::table('office')
                ->select('office_id', 'office_name')
                ->where('is_active', true)
                ->orderBy('office_name')
                ->get();

            return response()->json([
                'success' => true,
                'offices' => $offices,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load offices',
            ], 500);
        }
    }

    /**
     * Save visitor capture (face + ID image) to storage.
     */
    public function saveCapture(Request $request)
    {
        try {
            $step = $request->input('step', 1);

            $type = 'jpg';
            $binaryImage = null;

            // Preferred path: multipart file upload
            if ($request->hasFile('image')) {
                $uploadedImage = $request->file('image');
                if (! $uploadedImage || ! $uploadedImage->isValid()) {
                    return response()->json(['success' => false, 'message' => 'Invalid uploaded image file'], 400);
                }

                $mimeToExt = [
                    'image/jpeg' => 'jpg',
                    'image/jpg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                ];

                $mimeType = strtolower((string) $uploadedImage->getMimeType());
                if (! isset($mimeToExt[$mimeType])) {
                    return response()->json(['success' => false, 'message' => 'Unsupported uploaded image type'], 400);
                }

                $type = $mimeToExt[$mimeType];
                $binaryImage = file_get_contents($uploadedImage->getPathname());
            } else {
                // Backward-compatible path: base64 data URL
                $imageData = $request->input('image');

                if (! $imageData) {
                    return response()->json(['success' => false, 'message' => 'No image data provided'], 400);
                }

                if (preg_match('/data:image\/(\w+);base64,/', $imageData, $matches)) {
                    $base64Data = substr($imageData, strpos($imageData, ',') + 1);
                    $type = strtolower($matches[1]);

                    if (! in_array($type, ['jpeg', 'jpg', 'png', 'gif'])) {
                        return response()->json(['success' => false, 'message' => 'Invalid image type'], 400);
                    }

                    $binaryImage = base64_decode($base64Data, true);
                } else {
                    return response()->json(['success' => false, 'message' => 'Invalid image format'], 400);
                }
            }

            if (! $binaryImage) {
                return response()->json(['success' => false, 'message' => 'Failed to decode image'], 400);
            }

            if ($type === 'jpeg') {
                $type = 'jpg';
            }

            // Create picture directory if it doesn't exist
            $pictureDir = storage_path('app/public/captures');
            if (! is_dir($pictureDir)) {
                mkdir($pictureDir, 0755, true);
            }

            // Generate unique filename with timestamp
            $filename = 'capture_' . date('Y-m-d_H-i-s') . '_' . Str::random(8) . '.' . $type;
            $filePath = $pictureDir . '/' . $filename;

            // Save image to file
            if (file_put_contents($filePath, $binaryImage) === false) {
                return response()->json(['success' => false, 'message' => 'Failed to save image'], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Capture saved successfully',
                'filename' => $filename,
                'path' => $filePath,
                'step' => $step,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Parse ID document using OCR.Space API and extract visitor data.
     */
    public function parseId(Request $request)
    {
        try {
            $imageData = null;
            $source = 'unknown';

            // Handle file upload from FormData (preferred method)
            if ($request->hasFile('image')) {
                $imageData = $request->file('image');
                $source = 'file_upload';
                
                \Log::info('parseId: File upload received', [
                    'file_size' => $imageData->getSize(),
                    'mime_type' => $imageData->getMimeType(),
                ]);
            } else {
                // Fallback to base64 input (backward compatibility)
                $imageData = $request->input('image');
                $source = 'base64_input';
            }

            if (!$imageData) {
                return response()->json([
                    'success' => false,
                    'message' => 'No image data provided',
                ], 400);
            }

            $idType = $request->input('id_type', 'national');

            \Log::info('parseId called', [
                'source' => $source,
                'id_type' => $idType,
            ]);

            // Use OCRService to extract data
            $ocrService = new OCRService();
            $ocrResult = $ocrService->parseIdDocument($imageData, $idType);

            \Log::info('OCR result', [
                'success' => $ocrResult['success'],
                'message' => $ocrResult['message'] ?? '',
                'has_extracted_data' => !empty($ocrResult['extracted_data']),
            ]);

            if (!$ocrResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $ocrResult['message'],
                    'raw_text' => $ocrResult['raw_text'],
                ], 400);
            }

            $extracted = $ocrResult['extracted_data'];

            // Map OCR extracted fields to form field names
            $formData = $this->mapOcrDataToFormFields($extracted);

            \Log::info('Mapped form data', [
                'first_name' => $formData['first_name'] ?? '',
                'last_name' => $formData['last_name'] ?? '',
                'house_no' => $formData['house_no'] ?? '',
                'street' => $formData['street'] ?? '',
                'barangay' => $formData['barangay'] ?? '',
                'city_municipality' => $formData['city_municipality'] ?? '',
                'province' => $formData['province'] ?? '',
                'region' => $formData['region'] ?? '',
            ]);

            return response()->json([
                'success' => true,
                'extracted_data' => $extracted,
                'form_data' => $formData,
                'raw_text' => $ocrResult['raw_text'],
                'confidence' => $ocrResult['confidence'] ?? 0,
            ]);
        } catch (\Exception $e) {
            \Log::error('parseId exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error parsing ID: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Map OCR extracted data to visitor form field names.
     */
    protected function mapOcrDataToFormFields(array $extracted): array
    {
        // Prefer direct extracted name fields, fallback to full_name parsing
        $firstName = trim((string)($extracted['first_name'] ?? ''));
        $lastName = trim((string)($extracted['last_name'] ?? ''));

        if ((empty($firstName) || empty($lastName)) && !empty($extracted['full_name'])) {
            $parts = explode(',', $extracted['full_name']);
            if (count($parts) >= 2) {
                // Format: "LastName, FirstName"
                if (empty($lastName)) {
                    $lastName = trim($parts[0]);
                }
                if (empty($firstName)) {
                    $firstName = trim($parts[1]);
                }
            } else {
                // Try space-separated
                $nameParts = explode(' ', trim($extracted['full_name']));
                if (count($nameParts) > 1) {
                    if (empty($firstName)) {
                        $firstName = $nameParts[0];
                    }
                    if (empty($lastName)) {
                        $lastName = implode(' ', array_slice($nameParts, 1));
                    }
                } else {
                    if (empty($firstName)) {
                        $firstName = trim($extracted['full_name']);
                    }
                }
            }
        }

        // Parse address into components
        $addressData = $this->parseAddress($extracted['address'] ?? '');

        // Auto-infer PH region from extracted province when available
        if (empty($addressData['region']) && !empty($addressData['province'])) {
            $addressData['region'] = $this->inferRegionFromProvince($addressData['province']);
        }

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'house_no' => $addressData['house_no'],
            'street' => $addressData['street'],
            'barangay' => $addressData['barangay'],
            'city_municipality' => $addressData['city'],
            'province' => $addressData['province'],
            'region' => $addressData['region'],
        ];
    }

    /**
     * Parse address string into components.
     * Best-effort extraction for house/street/barangay/city/province/region.
     */
    protected function parseAddress(string $address): array
    {
        $result = [
            'house_no' => '',
            'street' => '',
            'barangay' => '',
            'city' => '',
            'province' => '',
            'region' => '',
        ];

        if (empty($address)) {
            return $result;
        }

        $normalizedAddress = $this->normalizeAddressForParsing($address);

        // Pattern seen in National ID OCR:
        // "0278 ROSAS ST MUNTING PULO, CITY OF LIPA, BATANGAS"
        if (preg_match('/^\s*(\d+[A-Z0-9-]*)\s+([A-Z\s]+?\b(?:ST|STREET|RD|ROAD|AVE|AVENUE|BLVD|BOULEVARD|LN|LANE)\b)\s+([A-Z][A-Z\s]+?)(?:,|$)/i', $normalizedAddress, $m)) {
            $result['house_no'] = trim($m[1]);
            $result['street'] = ucwords(strtolower(trim($m[2])));
            $result['barangay'] = ucwords(strtolower(trim($m[3])));
        }

        // Common National ID compact format example:
        // "PUROK 5, MUNTING PULO CITY OF LIPA BATANGAS"
        if (preg_match('/\bPUROK\s*\d+\s*,?\s*([A-Z][A-Z\s]+?)\s+CITY\s+OF\b/i', $normalizedAddress, $m)) {
            $result['barangay'] = ucwords(strtolower(trim($m[1])));
        }

        if (preg_match('/\bCITY\s+OF\s+([A-Z\s]+?)(?:\s+BATANGAS|\s+PROVINCE|$)/i', $normalizedAddress, $m)) {
            $result['city'] = 'City of ' . ucwords(strtolower(trim($m[1])));
        } elseif (preg_match('/\b([A-Z\s]+)\s+CITY\b/i', $normalizedAddress, $m)) {
            $result['city'] = ucwords(strtolower(trim($m[1]) . ' City'));
        }

        if (preg_match('/\bBATANGAS\b/i', $normalizedAddress)) {
            $result['province'] = 'Batangas';
        }

        // Ignore common OCR garbage-only address values.
        if (preg_match('/^(CLIKANGPILIPINA|PILIPINAS|REPUBLIKANGPILIPINAS)$/i', trim($address))) {
            return $result;
        }

        // Try to identify address components by keywords
        $addressLower = strtolower($normalizedAddress);
        $lines = array_filter(
            array_map('trim', explode(',', $normalizedAddress)),
            fn($line) => !empty($line)
        );

        // Handle OCR-noisy National ID address chunks like
        // "PUROK 5, MUNTING PULO, CITY OE IPA, BATANGAS".
        $remainingParts = [];
        foreach ($lines as $line) {
            $lineUpper = strtoupper($line);

            if (preg_match('/\bCITY\b/', $lineUpper)) {
                $normalizedCity = $this->normalizeCityFromOcrChunk($lineUpper, $normalizedAddress);
                if (!empty($normalizedCity)) {
                    $result['city'] = $normalizedCity;
                    continue;
                }
            }

            if (preg_match('/\bBATANGAS\b/i', $lineUpper)) {
                $result['province'] = 'Batangas';
                continue;
            }

            if (preg_match('/\bPUROK\s*\d+\b/i', $lineUpper)) {
                continue;
            }

            if (
                empty($result['barangay'])
                && preg_match('/^[A-Z\s]{3,}$/', $lineUpper)
                && !preg_match('/\b(CITY|MUNICIPALITY|PROVINCE|REGION|STREET|ROAD|AVENUE|PHL)\b/', $lineUpper)
            ) {
                $result['barangay'] = ucwords(strtolower(trim($lineUpper)));
                continue;
            }

            $remainingParts[] = $line;
        }

        foreach ($remainingParts as $line) {
            $lineLower = strtolower($line);

            if (preg_match('/^(?:no\.?|house|#)\s*(\d+[a-z]?|\w+[\w\s]*)/i', $line, $m)) {
                $result['house_no'] = trim($m[1]);
            } elseif (preg_match('/\b(st|street|ave|avenue|blvd|boulevard|lane|ln|road|rd)\b/i', $lineLower)) {
                if (preg_match('/^\s*(\d+[A-Z0-9-]*)\s+([A-Z\s]+?\b(?:ST|STREET|RD|ROAD|AVE|AVENUE|BLVD|BOULEVARD|LN|LANE)\b)\s+([A-Z][A-Z\s]+?)\s*$/i', $line, $m)) {
                    if (empty($result['house_no'])) {
                        $result['house_no'] = trim($m[1]);
                    }

                    if (empty($result['street'])) {
                        $result['street'] = ucwords(strtolower(trim($m[2])));
                    }

                    if (empty($result['barangay'])) {
                        $result['barangay'] = ucwords(strtolower(trim($m[3])));
                    }
                } elseif (empty($result['street'])) {
                    $result['street'] = trim($line);
                }
            } elseif (preg_match('/\bbarangay|brgy\b/i', $lineLower)) {
                if (preg_match('/(?:barangay|brgy)[.:\s]+([^,]+)/i', $line, $m)) {
                    $result['barangay'] = trim($m[1]);
                } else {
                    $result['barangay'] = trim(preg_replace('/^(?:barangay|brgy)[.:\s]*/i', '', $line));
                }
            } elseif (empty($result['city']) && preg_match('/\bcity|municipality|municipal|mun\b/i', $lineLower)) {
                if (preg_match('/(?:city|municipality|mun)[.:\s]+([^,]+)/i', $line, $m)) {
                    $result['city'] = trim($m[1]);
                } else {
                    $result['city'] = trim(preg_replace('/^(?:city|municipality|mun)[.:\s]*/i', '', $line));
                }
            } elseif (empty($result['province']) && preg_match('/\bprovince|prov\b/i', $lineLower)) {
                if (preg_match('/(?:province|prov)[.:\s]+([^,]+)/i', $line, $m)) {
                    $result['province'] = trim($m[1]);
                } else {
                    $result['province'] = trim(preg_replace('/^(?:province|prov)[.:\s]*/i', '', $line));
                }
            } elseif (preg_match('/\bregion|reg\b/i', $lineLower)) {
                if (preg_match('/(?:region|reg)[.:\s]+([^,]+)/i', $line, $m)) {
                    $result['region'] = trim($m[1]);
                } else {
                    $result['region'] = trim(preg_replace('/^(?:region|reg)[.:\s]*/i', '', $line));
                }
            }
        }

        // Pattern fallback for National ID address like: "PUROK 5, MUNTING PULO, CITY OF LIPA, BATANGAS"
        if (preg_match('/\b(CITY\s+OF\s+[A-Z\s]+|[A-Z\s]+\s+CITY)\b/i', $normalizedAddress, $m) && empty($result['city'])) {
            $result['city'] = ucwords(strtolower(trim($m[1])));
        }

        // Fallback: infer city/province from unlabeled trailing parts
        if (empty($result['province']) && !empty($lines)) {
            $lastPart = trim(end($lines));
            if (preg_match('/^[A-Za-z\s]{3,}$/', $lastPart) && !preg_match('/\b(city|municipality|barangay|brgy|street|st|road|rd|avenue|ave|pilipina)\b/i', $lastPart) && !preg_match('/^[A-Za-z]{12,}$/', $lastPart)) {
                $result['province'] = $lastPart;
            }
        }

        if (empty($result['city']) && !empty($lines)) {
            foreach ($lines as $line) {
                if (preg_match('/\b(city\s+of\s+[A-Za-z\s]+)\b/i', $line, $m)) {
                    $result['city'] = trim($m[1]);
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Normalize OCR text noise to make address parsing more reliable.
     */
    protected function normalizeAddressForParsing(string $address): string
    {
        $normalized = trim($address);

        if (function_exists('iconv')) {
            $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized);
            if ($ascii !== false) {
                $normalized = $ascii;
            }
        }

        $normalized = strtoupper($normalized);
        $normalized = preg_replace('/[^A-Z0-9,\s-]/', ' ', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        // Common OCR corruption around "CITY OF LIPA"
        $normalized = preg_replace('/\bCITY\s+OE\s+IPA\b/', 'CITY OF LIPA', $normalized);
        $normalized = preg_replace('/\bCITY\s+O\s+IPA\b/', 'CITY OF LIPA', $normalized);
        $normalized = preg_replace('/\bCITY\s+OF\s+IPA\b/', 'CITY OF LIPA', $normalized);
        $normalized = preg_replace('/\bCHY\s+OF\s+LIPA\b/', 'CITY OF LIPA', $normalized);
        $normalized = preg_replace('/\bCHY\s+OF\b/', 'CITY OF', $normalized);

        return trim($normalized);
    }

    /**
     * Build a clean city value from OCR-noisy city chunks.
     */
    protected function normalizeCityFromOcrChunk(string $cityChunk, string $fullAddress): string
    {
        if (preg_match('/\bLIPA\b/', $cityChunk)) {
            return 'City of Lipa';
        }

        if (preg_match('/\bCITY\s+OF\s+([A-Z\s]+)\b/', $cityChunk, $m)) {
            return 'City of ' . ucwords(strtolower(trim($m[1])));
        }

        if (preg_match('/\bCITY\s+([A-Z\s]+)\b/', $cityChunk, $m)) {
            $candidate = trim($m[1]);

            if (preg_match('/\bIPA\b/', $candidate) && preg_match('/\bBATANGAS\b/', $fullAddress)) {
                return 'City of Lipa';
            }

            return 'City of ' . ucwords(strtolower($candidate));
        }

        return '';
    }

    /**
     * Infer Philippine region from province name.
     */
    protected function inferRegionFromProvince(string $province): string
    {
        $normalized = strtolower(trim($province));
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        $map = [
            // NCR
            'metro manila' => 'NCR',
            'manila' => 'NCR',

            // CAR
            'abra' => 'CAR',
            'apayao' => 'CAR',
            'benguet' => 'CAR',
            'ifugao' => 'CAR',
            'kalinga' => 'CAR',
            'mountain province' => 'CAR',

            // Region I - Ilocos Region
            'ilocos norte' => 'Region I',
            'ilocos sur' => 'Region I',
            'la union' => 'Region I',
            'pangasinan' => 'Region I',

            // Region II - Cagayan Valley
            'batanes' => 'Region II',
            'cagayan' => 'Region II',
            'isabela' => 'Region II',
            'nueva vizcaya' => 'Region II',
            'quirino' => 'Region II',

            // Region III - Central Luzon
            'aurora' => 'Region III',
            'bataan' => 'Region III',
            'bulacan' => 'Region III',
            'nueva ecija' => 'Region III',
            'pampanga' => 'Region III',
            'tarlac' => 'Region III',
            'zambales' => 'Region III',

            // Region IV-A - CALABARZON
            'batangas' => 'Region IV-A',
            'cavite' => 'Region IV-A',
            'laguna' => 'Region IV-A',
            'quezon' => 'Region IV-A',
            'rizal' => 'Region IV-A',

            // Region IV-B - MIMAROPA
            'marinduque' => 'Region IV-B',
            'occidental mindoro' => 'Region IV-B',
            'oriental mindoro' => 'Region IV-B',
            'palawan' => 'Region IV-B',
            'romblon' => 'Region IV-B',

            // Region V - Bicol Region
            'albay' => 'Region V',
            'camarines norte' => 'Region V',
            'camarines sur' => 'Region V',
            'catanduanes' => 'Region V',
            'masbate' => 'Region V',
            'sorsogon' => 'Region V',

            // Region VI - Western Visayas
            'aklan' => 'Region VI',
            'antique' => 'Region VI',
            'capiz' => 'Region VI',
            'guimaras' => 'Region VI',
            'iloilo' => 'Region VI',
            'negros occidental' => 'Region VI',

            // Region VII - Central Visayas
            'bohol' => 'Region VII',
            'cebu' => 'Region VII',
            'negros oriental' => 'Region VII',
            'siquijor' => 'Region VII',

            // Region VIII - Eastern Visayas
            'biliran' => 'Region VIII',
            'eastern samar' => 'Region VIII',
            'leyte' => 'Region VIII',
            'northern samar' => 'Region VIII',
            'samar' => 'Region VIII',
            'southern leyte' => 'Region VIII',

            // Region IX - Zamboanga Peninsula
            'zamboanga del norte' => 'Region IX',
            'zamboanga del sur' => 'Region IX',
            'zamboanga sibugay' => 'Region IX',

            // Region X - Northern Mindanao
            'bukidnon' => 'Region X',
            'camiguin' => 'Region X',
            'lanao del norte' => 'Region X',
            'misamis occidental' => 'Region X',
            'misamis oriental' => 'Region X',

            // Region XI - Davao Region
            'davao de oro' => 'Region XI',
            'davao del norte' => 'Region XI',
            'davao del sur' => 'Region XI',
            'davao occidental' => 'Region XI',
            'davao oriental' => 'Region XI',

            // Region XII - SOCCSKSARGEN
            'cotabato' => 'Region XII',
            'sarangani' => 'Region XII',
            'south cotabato' => 'Region XII',
            'sultan kudarat' => 'Region XII',

            // Region XIII - Caraga
            'agusan del norte' => 'Region XIII',
            'agusan del sur' => 'Region XIII',
            'dinagat islands' => 'Region XIII',
            'surigao del norte' => 'Region XIII',
            'surigao del sur' => 'Region XIII',

            // BARMM
            'basilan' => 'BARMM',
            'lanao del sur' => 'BARMM',
            'maguindanao del norte' => 'BARMM',
            'maguindanao del sur' => 'BARMM',
            'sulu' => 'BARMM',
            'tawi-tawi' => 'BARMM',
            'cotabato city' => 'BARMM',
        ];

        return $map[$normalized] ?? '';
    }
}