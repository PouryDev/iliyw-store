<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CheckoutUploadController extends Controller
{
    /**
     * Store a temporary upload for a cart item key.
     */
    public function storeTemp(Request $request, string $cartKey)
    {
        $validated = $request->validate([
            // accept common mobile audio mimetypes; allow octet-stream fallback
            'file' => 'required|file|max:51200|mimetypes:audio/*,application/octet-stream', // 50MB
        ]);

        $sessionId = $request->session()->getId();

        // Enforce max 10 files per cartKey in temp
        $tempBasePath = "order_uploads/tmp/{$sessionId}/{$cartKey}";
        $existing = collect(Storage::disk('private')->files($tempBasePath));
        if ($existing->count() >= 10) {
            return response()->json([
                'success' => false,
                'message' => 'حداکثر 10 فایل مجاز است.'
            ], 422);
        }

        $file = $validated['file'];
        $original = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();
        $size = $file->getSize();

        // Fallback extension if empty (e.g., octet-stream without extension)
        if (!$ext) { $ext = 'bin'; }
        $randomName = now()->timestamp . '_' . Str::random(8) . '.' . $ext;
        $storedPath = $file->storeAs($tempBasePath, $randomName, 'private');

        $tempId = sha1($storedPath);

        return response()->json([
            'success' => true,
            'data' => [
                'temp_id' => $tempId,
                'path' => $storedPath,
                'original_name' => $original,
                'mime' => $mime,
                'size' => $size,
                'type' => in_array($ext, ['mp3','wav','ogg']) ? 'audio' : 'image',
            ]
        ]);
    }

    /**
     * Delete a temporary upload by temp id (path hash) for a cart item key.
     */
    public function destroyTemp(Request $request, string $cartKey, string $tempId)
    {
        $sessionId = $request->session()->getId();
        $tempBasePath = "order_uploads/tmp/{$sessionId}/{$cartKey}";
        $files = Storage::disk('private')->files($tempBasePath);
        foreach ($files as $path) {
            if (sha1($path) === $tempId) {
                Storage::disk('private')->delete($path);
                return response()->json(['success' => true]);
            }
        }
        return response()->json(['success' => false, 'message' => 'فایل یافت نشد'], 404);
    }
}


