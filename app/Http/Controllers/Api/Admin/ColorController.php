<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ColorController extends Controller
{
    /**
     * Get all colors (admin)
     */
    public function index(): JsonResponse
    {
        $colors = Color::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $colors
        ]);
    }

    /**
     * Store new color
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'hex_code' => 'nullable|string|max:7',
            'is_active' => 'boolean',
        ]);

        try {
            $color = Color::create($request->only(['name', 'hex_code', 'is_active']));

            return response()->json([
                'success' => true,
                'message' => 'رنگ با موفقیت ایجاد شد',
                'data' => $color
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ایجاد رنگ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show color details
     */
    public function show(int $id): JsonResponse
    {
        $color = Color::find($id);

        if (!$color) {
            return response()->json([
                'success' => false,
                'message' => 'رنگ یافت نشد'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $color
        ]);
    }

    /**
     * Update color
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'hex_code' => 'nullable|string|max:7',
            'is_active' => 'sometimes|boolean',
        ]);

        try {
            $color = Color::find($id);

            if (!$color) {
                return response()->json([
                    'success' => false,
                    'message' => 'رنگ یافت نشد'
                ], 404);
            }

            $color->update($request->only(['name', 'hex_code', 'is_active']));

            return response()->json([
                'success' => true,
                'message' => 'رنگ با موفقیت به‌روزرسانی شد',
                'data' => $color
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در به‌روزرسانی رنگ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete color
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $color = Color::find($id);

            if (!$color) {
                return response()->json([
                    'success' => false,
                    'message' => 'رنگ یافت نشد'
                ], 404);
            }

            // Check if color is used in any product variants
            if ($color->productVariants()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'این رنگ در محصولات استفاده شده و قابل حذف نیست'
                ], 400);
            }

            $color->delete();

            return response()->json([
                'success' => true,
                'message' => 'رنگ با موفقیت حذف شد'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف رنگ: ' . $e->getMessage()
            ], 500);
        }
    }
}

