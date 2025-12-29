<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SizeController extends Controller
{
    /**
     * Get all sizes (admin)
     */
    public function index(): JsonResponse
    {
        $sizes = Size::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $sizes
        ]);
    }

    /**
     * Store new size
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        try {
            $size = Size::create($request->only(['name', 'sort_order', 'is_active']));

            return response()->json([
                'success' => true,
                'message' => 'سایز با موفقیت ایجاد شد',
                'data' => $size
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ایجاد سایز: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show size details
     */
    public function show(int $id): JsonResponse
    {
        $size = Size::find($id);

        if (!$size) {
            return response()->json([
                'success' => false,
                'message' => 'سایز یافت نشد'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $size
        ]);
    }

    /**
     * Update size
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        try {
            $size = Size::find($id);

            if (!$size) {
                return response()->json([
                    'success' => false,
                    'message' => 'سایز یافت نشد'
                ], 404);
            }

            $size->update($request->only(['name', 'sort_order', 'is_active']));

            return response()->json([
                'success' => true,
                'message' => 'سایز با موفقیت به‌روزرسانی شد',
                'data' => $size
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در به‌روزرسانی سایز: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete size
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $size = Size::find($id);

            if (!$size) {
                return response()->json([
                    'success' => false,
                    'message' => 'سایز یافت نشد'
                ], 404);
            }

            // Check if size is used in any product variants
            if ($size->productVariants()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'این سایز در محصولات استفاده شده و قابل حذف نیست'
                ], 400);
            }

            $size->delete();

            return response()->json([
                'success' => true,
                'message' => 'سایز با موفقیت حذف شد'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف سایز: ' . $e->getMessage()
            ], 500);
        }
    }
}

