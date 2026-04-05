<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GuardVisitorController extends Controller
{
    /**
     * Save visitor capture (face + ID image) to storage.
     */
    public function saveCapture(Request $request)
    {
        try {
            $imageData = $request->input('image');
            $step = $request->input('step', 1);

            if (! $imageData) {
                return response()->json(['success' => false, 'message' => 'No image data provided'], 400);
            }

            // Decode base64 image
            if (preg_match('/data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]); // jpeg, png, gif, etc.

                if (! in_array($type, ['jpeg', 'jpg', 'png', 'gif'])) {
                    return response()->json(['success' => false, 'message' => 'Invalid image type'], 400);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'Invalid image format'], 400);
            }

            $imageData = base64_decode($imageData);

            if (! $imageData) {
                return response()->json(['success' => false, 'message' => 'Failed to decode image'], 400);
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
            if (file_put_contents($filePath, $imageData) === false) {
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
}