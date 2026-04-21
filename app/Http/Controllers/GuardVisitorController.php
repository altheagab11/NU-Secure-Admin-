<?php

namespace App\Http\Controllers;

use App\Services\OCRService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class GuardVisitorController extends Controller
{
    /**
     * Persist visitor registration data (visitor + visit + optional visit_route).
     */
    public function storeVisitorRegistration(Request $request)
    {
        $validated = $request->validate([
            'register_type' => ['required', 'string'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'house_no' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'barangay' => ['required', 'string', 'max:255'],
            'city_municipality' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'region' => ['required', 'string', 'max:255'],
            'contact_no' => ['required', 'string', 'max:20'],
            'pass_number' => ['required', 'string', 'max:255'],
            'control_number' => ['required', 'string', 'max:255'],
            'purpose_reason' => ['required', 'string', 'max:2000'],
            'office_ids' => ['required', 'array', 'min:1'],
            'office_ids.*' => ['integer'],
            'visitor_photo_with_id_url' => ['nullable', 'string', 'max:2000'],
        ]);

        if (strtolower((string) $validated['register_type']) !== 'normal') {
            return response()->json([
                'success' => false,
                'message' => 'Only Normal Visitor save flow is enabled right now.',
            ], 422);
        }

        $officeIds = array_values(array_unique(array_map('intval', $validated['office_ids'])));
        if (empty($officeIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Please select at least one destination office.',
            ], 422);
        }

        $activeExitStatusId = $this->resolveExitStatusId();
        $visitorVisitTypeId = $this->resolveVisitTypeId('Visitor');
        $pendingRouteStatusId = $this->resolveRouteStatusId('PENDING');

        if (! $activeExitStatusId) {
            return response()->json([
                'success' => false,
                'message' => 'Exit status "Active" is not configured.',
            ], 500);
        }

        if (! $visitorVisitTypeId) {
            return response()->json([
                'success' => false,
                'message' => 'Visit type "Visitor" is not configured.',
            ], 500);
        }

        try {
            $result = DB::transaction(function () use ($validated, $officeIds, $activeExitStatusId, $visitorVisitTypeId, $pendingRouteStatusId) {
                $matchedVisitor = $this->findVisitorForRegistration($validated);

                $addressPayload = [
                    'house_no' => $validated['house_no'],
                    'street' => $validated['street'],
                    'barangay' => $validated['barangay'],
                    'city_municipality' => $validated['city_municipality'],
                    'province' => $validated['province'],
                    'region' => $validated['region'],
                ];

                $addressId = $this->resolveAddressIdForRegistration($addressPayload);

                $visitorAction = 'created_new';

                if ($matchedVisitor) {
                    $visitorId = (int) $matchedVisitor->visitor_id;

                    $photoPath = trim((string) ($validated['visitor_photo_with_id_url'] ?? ''));

                    DB::table('visitor')
                        ->where('visitor_id', $visitorId)
                        ->update([
                            'first_name' => $validated['first_name'],
                            'last_name' => $validated['last_name'],
                            'address_id' => $addressId,
                            'contact_no' => $validated['contact_no'],
                            'pass_number' => $validated['pass_number'],
                            'control_number' => $validated['control_number'],
                            'visitor_photo_with_id_url' => $photoPath !== ''
                                ? $photoPath
                                : ($matchedVisitor->visitor_photo_with_id_url ?? null),
                        ]);

                    $visitorAction = 'updated_existing';
                } else {
                    $visitorId = DB::table('visitor')->insertGetId([
                        'first_name' => $validated['first_name'],
                        'last_name' => $validated['last_name'],
                        'address_id' => $addressId,
                        'contact_no' => $validated['contact_no'],
                        'pass_number' => $validated['pass_number'],
                        'control_number' => $validated['control_number'],
                        'visitor_photo_with_id_url' => $validated['visitor_photo_with_id_url'] ?? null,
                        'created_at' => now(),
                    ], 'visitor_id');
                }

                $visitId = DB::table('visit')->insertGetId([
                    'visitor_id' => $visitorId,
                    'guard_user_id' => optional(request()->user())->id,
                    'visit_type_id' => $visitorVisitTypeId,
                    'purpose_reason' => $validated['purpose_reason'],
                    'primary_office_id' => $officeIds[0],
                    'qr_token' => strtoupper(Str::random(12)),
                    'entry_time' => now(),
                    'exit_status_id' => $activeExitStatusId,
                ], 'visit_id');

                $savedOfficeCount = 0;

                // For multi-office visitors, use office_expectation table (not visit_route which is for enrollee steps)
                if (count($officeIds) > 1) {
                    $expectationRows = [];
                    foreach ($officeIds as $officeId) {
                        $expectationRows[] = [
                            'visit_id' => $visitId,
                            'office_id' => $officeId,
                        ];
                    }

                    DB::table('office_expectation')->insert($expectationRows);
                    $savedOfficeCount = count($expectationRows);
                }

                return [
                    'address_id' => $addressId,
                    'visitor_id' => $visitorId,
                    'visitor_action' => $visitorAction,
                    'visit_id' => $visitId,
                    'primary_office_id' => $officeIds[0],
                    'saved_office_count' => $savedOfficeCount,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Visitor details saved successfully.',
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            \Log::error('storeVisitorRegistration failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save visitor details.',
            ], 500);
        }
    }

    /**
     * Resolve existing address row by exact normalized fields or create a new one.
     */
    protected function resolveAddressIdForRegistration(array $addressPayload): int
    {
        $normalized = [
            'house_no' => trim((string) ($addressPayload['house_no'] ?? '')),
            'street' => trim((string) ($addressPayload['street'] ?? '')),
            'barangay' => trim((string) ($addressPayload['barangay'] ?? '')),
            'city_municipality' => trim((string) ($addressPayload['city_municipality'] ?? '')),
            'province' => trim((string) ($addressPayload['province'] ?? '')),
            'region' => trim((string) ($addressPayload['region'] ?? '')),
        ];

        $existing = DB::table('address')
            ->select('address_id')
            ->whereRaw("LOWER(TRIM(COALESCE(house_no, ''))) = ?", [Str::lower($normalized['house_no'])])
            ->whereRaw("LOWER(TRIM(COALESCE(street, ''))) = ?", [Str::lower($normalized['street'])])
            ->whereRaw("LOWER(TRIM(COALESCE(barangay, ''))) = ?", [Str::lower($normalized['barangay'])])
            ->whereRaw("LOWER(TRIM(COALESCE(city_municipality, ''))) = ?", [Str::lower($normalized['city_municipality'])])
            ->whereRaw("LOWER(TRIM(COALESCE(province, ''))) = ?", [Str::lower($normalized['province'])])
            ->whereRaw("LOWER(TRIM(COALESCE(region, ''))) = ?", [Str::lower($normalized['region'])])
            ->orderByDesc('address_id')
            ->first();

        if ($existing) {
            return (int) $existing->address_id;
        }

        return (int) DB::table('address')->insertGetId($normalized, 'address_id');
    }

    /**
     * Find existing visitor for registration dedup by exact first+last name.
     */
    protected function findVisitorForRegistration(array $validated): ?object
    {
        $firstName = trim((string) ($validated['first_name'] ?? ''));
        $lastName = trim((string) ($validated['last_name'] ?? ''));

        $baseQuery = static function () {
            return DB::table('visitor')
                ->select('visitor_id', 'address_id', 'visitor_photo_with_id_url')
                ->orderByDesc('visitor_id');
        };

        if ($firstName === '' || $lastName === '') {
            return null;
        }

        return $baseQuery()
            ->whereRaw("LOWER(TRIM(COALESCE(first_name, ''))) = ?", [Str::lower($firstName)])
            ->whereRaw("LOWER(TRIM(COALESCE(last_name, ''))) = ?", [Str::lower($lastName)])
            ->first();
    }

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
            $step = (int) $request->input('step', 1);

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

            // Keep ID scan (step 1) local so OCR flow remains reliable even when storage RLS is strict.
            if ($step !== 3) {
                $localResult = $this->saveCaptureLocally($binaryImage, $type);

                if (! $localResult['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => $localResult['message'] ?? 'Failed to save ID scan image',
                    ], 500);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Capture saved successfully',
                    'filename' => $localResult['filename'],
                    'path' => $localResult['public_path'],
                    'public_url' => $localResult['public_path'],
                    'bucket' => null,
                    'bucket_file_path' => null,
                    'step' => $step,
                ]);
            }

            $uploadResult = $this->uploadCaptureToSupabase($binaryImage, $type, $step);
            if (! $uploadResult['success']) {
                // Fallback: do not block registration when Supabase Storage key/policy is misconfigured.
                $localResult = $this->saveCaptureLocally($binaryImage, $type);

                if (! $localResult['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => ($uploadResult['message'] ?? 'Failed to upload image to Supabase') . ' Also failed local fallback save.',
                    ], 500);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Capture saved locally. Supabase upload is currently blocked.',
                    'filename' => $localResult['filename'],
                    'path' => $localResult['public_path'],
                    'public_url' => $localResult['public_path'],
                    'bucket' => null,
                    'bucket_file_path' => null,
                    'warning' => $uploadResult['message'] ?? 'Supabase upload failed',
                    'step' => $step,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Capture uploaded successfully',
                'filename' => $uploadResult['filename'],
                'path' => $uploadResult['object_path'],
                'public_url' => $uploadResult['public_url'],
                'bucket' => $uploadResult['bucket'],
                'bucket_file_path' => $uploadResult['bucket_file_path'],
                'step' => $step,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Save capture to local public storage and return file metadata.
     */
    protected function saveCaptureLocally(string $binaryImage, string $extension): array
    {
        $pictureDir = storage_path('app/public/captures');
        if (! is_dir($pictureDir) && ! mkdir($pictureDir, 0755, true) && ! is_dir($pictureDir)) {
            return [
                'success' => false,
                'message' => 'Failed to create local capture directory.',
            ];
        }

        $filename = 'capture_' . date('Y-m-d_H-i-s') . '_' . Str::random(8) . '.' . $extension;
        $filePath = $pictureDir . '/' . $filename;

        if (file_put_contents($filePath, $binaryImage) === false) {
            return [
                'success' => false,
                'message' => 'Failed to write local capture file.',
            ];
        }

        return [
            'success' => true,
            'filename' => $filename,
            'absolute_path' => $filePath,
            'public_path' => '/storage/captures/' . $filename,
        ];
    }

    /**
     * Upload capture to Supabase Storage and return stored object details.
     */
    protected function uploadCaptureToSupabase(string $binaryImage, string $extension, int $step): array
    {
        $supabaseUrl = rtrim((string) env('SUPABASE_URL', ''), '/');
        $supabaseKey = (string) (env('SUPABASE_STORAGE_KEY') ?: env('SUPABASE_SERVICE_ROLE_KEY') ?: env('SUPABASE_KEY'));

        if ($supabaseUrl === '' || $supabaseKey === '') {
            return [
                'success' => false,
                'message' => 'Supabase configuration is missing.',
            ];
        }

        $bucket = (string) env('SUPABASE_STORAGE_BUCKET', 'visitor-files');
        $defaultFolder = $step === 3 ? 'Face_ID_picture' : 'ID_scan';
        $folder = trim((string) env('SUPABASE_STORAGE_FACE_ID_FOLDER', $defaultFolder), '/');

        $filename = 'capture_' . date('Y-m-d_H-i-s') . '_' . Str::random(8) . '.' . $extension;
        $objectPath = $folder !== '' ? ($folder . '/' . $filename) : $filename;

        $contentType = match ($extension) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };

        $encodedPath = collect(explode('/', $objectPath))
            ->filter(fn ($segment) => $segment !== '')
            ->map(fn ($segment) => rawurlencode($segment))
            ->implode('/');

        $uploadUrl = $supabaseUrl . '/storage/v1/object/' . rawurlencode($bucket) . '/' . $encodedPath;

        $response = Http::withHeaders([
            'apikey' => $supabaseKey,
            'Authorization' => 'Bearer ' . $supabaseKey,
            'Content-Type' => $contentType,
            'x-upsert' => 'true',
        ])->withBody($binaryImage, $contentType)->post($uploadUrl);

        if (! $response->successful()) {
            \Log::error('Supabase capture upload failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'bucket' => $bucket,
                'object_path' => $objectPath,
                'step' => $step,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to upload capture to Supabase Storage (check bucket RLS policy or service role key).',
            ];
        }

        return [
            'success' => true,
            'bucket' => $bucket,
            'filename' => $filename,
            'object_path' => $objectPath,
            'public_url' => $supabaseUrl . '/storage/v1/object/public/' . rawurlencode($bucket) . '/' . $encodedPath,
            'bucket_file_path' => $bucket . '/' . $objectPath,
        ];
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
            $existingVisitor = $this->findExistingVisitorRecord($formData, $extracted);

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
                'existing_visitor' => $existingVisitor,
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
     * Try to find an existing visitor by exact first+last name.
     */
    protected function findExistingVisitorRecord(array $formData, array $extracted): array
    {
        $firstName = trim((string) ($formData['first_name'] ?? ''));
        $lastName = trim((string) ($formData['last_name'] ?? ''));

        $baseQuery = static function () {
            return DB::table('visitor as v')
                ->leftJoin('address as a', 'a.address_id', '=', 'v.address_id')
                ->select([
                    'v.visitor_id',
                    'v.first_name',
                    'v.last_name',
                    'v.contact_no',
                    'v.pass_number',
                    'v.visitor_photo_with_id_url',
                    'a.house_no',
                    'a.street',
                    'a.barangay',
                    'a.city_municipality',
                    'a.province',
                    'a.region',
                    'v.created_at',
                ]);
        };

        if ($firstName === '' || $lastName === '') {
            return ['exists' => false];
        }

        $record = $baseQuery()
            ->whereRaw("LOWER(TRIM(COALESCE(v.first_name, ''))) = ?", [Str::lower($firstName)])
            ->whereRaw("LOWER(TRIM(COALESCE(v.last_name, ''))) = ?", [Str::lower($lastName)])
            ->orderByDesc('v.created_at')
            ->orderByDesc('v.visitor_id')
            ->first();

        if (! $record) {
            return ['exists' => false];
        }

        $photoPath = trim((string) ($record->visitor_photo_with_id_url ?? ''));

        return [
            'exists' => true,
            'match_basis' => 'name',
            'visitor_id' => (int) $record->visitor_id,
            'first_name' => (string) ($record->first_name ?? ''),
            'last_name' => (string) ($record->last_name ?? ''),
            'contact_no' => (string) ($record->contact_no ?? ''),
            'pass_number' => (string) ($record->pass_number ?? ''),
            'house_no' => (string) ($record->house_no ?? ''),
            'street' => (string) ($record->street ?? ''),
            'barangay' => (string) ($record->barangay ?? ''),
            'city_municipality' => (string) ($record->city_municipality ?? ''),
            'province' => (string) ($record->province ?? ''),
            'region' => (string) ($record->region ?? ''),
            'photo_path' => $photoPath,
            'photo_preview_url' => $this->resolveVisitorPhotoUrl($photoPath),
        ];
    }

    /**
     * Resolve stored visitor photo path to a browser-displayable URL when possible.
     */
    protected function resolveVisitorPhotoUrl(string $photoPath): ?string
    {
        $cleanPath = trim($photoPath);
        if ($cleanPath === '') {
            return null;
        }

        if (Str::startsWith($cleanPath, ['http://', 'https://'])) {
            return $cleanPath;
        }

        if (! str_contains($cleanPath, '/')) {
            return null;
        }

        [$bucket, $objectPath] = array_pad(explode('/', $cleanPath, 2), 2, '');
        $bucket = trim($bucket);
        $objectPath = trim($objectPath);

        if ($bucket === '' || $objectPath === '') {
            return null;
        }

        $supabaseUrl = rtrim((string) env('SUPABASE_URL', ''), '/');
        if ($supabaseUrl === '') {
            return null;
        }

        $encodedPath = implode('/', array_map('rawurlencode', explode('/', $objectPath)));

        return $supabaseUrl . '/storage/v1/object/public/' . rawurlencode($bucket) . '/' . $encodedPath;
    }

    /**
     * Map OCR extracted data to visitor form field names.
     */
    protected function mapOcrDataToFormFields(array $extracted): array
    {
        // Prefer direct extracted name fields, fallback to full_name parsing
        $firstName = trim((string)($extracted['first_name'] ?? ''));
        $lastName = trim((string)($extracted['last_name'] ?? ''));
        $documentType = strtolower(trim((string)($extracted['document_type'] ?? '')));

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
                if ($documentType === 'passport') {
                    if (empty($firstName)) {
                        $firstName = trim($extracted['full_name']);
                    }

                    // For passports, do not guess a surname from a space-separated given name.
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
        }

        // Parse address into components
        $addressSource = trim((string)($extracted['address'] ?? ''));

        if (
            !empty($addressSource)
            && (
                (($extracted['document_type'] ?? '') === 'passport')
                || preg_match('/\b(PLACE\s+OF\s+B|BIRTH\s+PLACE|POB|PLOCE)\b/i', $addressSource)
            )
        ) {
            $addressSource = $this->normalizePassportPlaceOfBirthSource($addressSource);
        }

        if (empty($addressSource) && !empty($extracted['place_of_birth'])) {
            $addressSource = $this->normalizePassportPlaceOfBirthSource((string)$extracted['place_of_birth']);
        }

        $addressData = $this->parseAddress($addressSource);

        if (empty($addressData['city']) && !empty($extracted['place_of_birth'])) {
            $birthplaceData = $this->parseAddress($this->normalizePassportPlaceOfBirthSource((string)$extracted['place_of_birth']));
            if (!empty($birthplaceData['city'])) {
                $addressData['city'] = $birthplaceData['city'];
            }
            if (empty($addressData['province']) && !empty($birthplaceData['province'])) {
                $addressData['province'] = $birthplaceData['province'];
            }
            if (empty($addressData['region']) && !empty($birthplaceData['region'])) {
                $addressData['region'] = $birthplaceData['region'];
            }
        }

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
     * Strip passport birthplace label noise so the city parser can work on the real municipality.
     */
    protected function normalizePassportPlaceOfBirthSource(string $placeOfBirth): string
    {
        $normalized = $this->normalizeAddressForParsing($placeOfBirth);

        $normalized = preg_replace('/\b(PLACE\s+OF\s+B(?:IRTH|ERTE)|BIRTH\s+PLACE|PLACE\s+OF\s+BIRTH|POB)\b/i', ' ', $normalized);
        $normalized = preg_replace('/\b[A-Z]\b/', ' ', $normalized);
        $normalized = preg_replace('/\s+/', ' ', trim((string)$normalized));

        if (preg_match('/\bCITY\s+OF\s+([A-Z\s]{2,40})\b/', $normalized, $m)) {
            return 'CITY OF ' . trim($m[1]);
        }

        if (preg_match('/\b([A-Z\s]{2,40})\s+CITY\b/', $normalized, $m)) {
            return trim($m[1]) . ' CITY';
        }

        if (preg_match('/\b(CALAPAN|LIPA|BATANGAS|MINDORO|PUERTO|ORIENTAL|OCCIDENTAL)\b/i', $normalized, $m)) {
            $candidate = strtoupper(trim($m[1]));
            if ($candidate === 'LIPA') {
                return 'CITY OF LIPA';
            }

            if ($candidate === 'CALAPAN') {
                return 'CALAPAN CITY';
            }

            return $candidate;
        }

        return trim((string)$normalized);
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
        $parts = array_values(array_filter(array_map('trim', explode(',', $normalizedAddress)), fn($part) => $part !== ''));

        // Village-style pattern, e.g.:
        // "ROAD 29, BLOCK 31, LOT 16, BULATI, STREET BANAYBANAY, LIPA CITY, BATANGAS"
        $houseParts = [];
        foreach ($parts as $idx => $part) {
            if (preg_match('/^(ROAD|BLOCK|LOT)\s+[A-Z0-9-]+$/i', $part)) {
                $houseParts[] = strtoupper($part);
                continue;
            }

            if (preg_match('/^STREET\s+([A-Z\s]+?)(?:\s+[A-Z]+\s+CITY|\s+CITY|\s+MUNICIPALITY|$)/i', $part, $m)) {
                if (empty($result['street']) && isset($parts[$idx - 1])) {
                    $prevPart = trim((string)$parts[$idx - 1]);
                    if (
                        !empty($prevPart)
                        && !preg_match('/^(ROAD|BLOCK|LOT)\s+/i', $prevPart)
                        && !preg_match('/\b(CITY|MUNICIPALITY|PROVINCE|REGION|BATANGAS)\b/i', $prevPart)
                    ) {
                        $result['street'] = ucwords(strtolower($prevPart));
                    }
                }

                if (empty($result['barangay'])) {
                    $result['barangay'] = ucwords(strtolower(trim($m[1])));
                }
            }
        }

        if (!empty($houseParts) && empty($result['house_no'])) {
            $result['house_no'] = implode(', ', $houseParts);
        }

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

    protected function resolveVisitTypeId(string $visitTypeName): ?int
    {
        $exact = DB::table('visit_type')
            ->whereRaw('LOWER(visit_type_name) = ?', [strtolower($visitTypeName)])
            ->value('visit_type_id');

        if ($exact) {
            return (int) $exact;
        }

        return null;
    }

    protected function resolveExitStatusId(): ?int
    {
        $exact = DB::table('exit_status')
            ->whereRaw('LOWER(exit_status_name) = ?', ['active'])
            ->value('exit_status_id');

        if ($exact) {
            return (int) $exact;
        }

        $fallback = DB::table('exit_status')
            ->whereRaw('LOWER(exit_status_name) like ?', ['%active%'])
            ->value('exit_status_id');

        return $fallback ? (int) $fallback : 3;
    }

    protected function resolveRouteStatusId(string $statusName): ?int
    {
        $exact = DB::table('route_status')
            ->whereRaw('LOWER(route_status_name) = ?', [strtolower($statusName)])
            ->value('route_status_id');

        if ($exact) {
            return (int) $exact;
        }

        $fallback = DB::table('route_status')
            ->orderBy('route_status_id')
            ->value('route_status_id');

        return $fallback ? (int) $fallback : null;
    }
}