<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\DeliveryMethodResource;
use App\Models\DeliveryMethod;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DeliveryMethodController extends Controller
{
    /**
     * Get all delivery methods
     */
    public function index(Request $request): JsonResponse
    {
        $deliveryMethods = DeliveryMethod::orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'data' => DeliveryMethodResource::collection($deliveryMethods)
        ]);
    }

    /**
     * Store new delivery method
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'estimated_days' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        try {
            $deliveryMethod = DeliveryMethod::create($request->only([
                'name', 'description', 'price', 'estimated_days', 'is_active', 'sort_order'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'روش ارسال با موفقیت ایجاد شد',
                'data' => new DeliveryMethodResource($deliveryMethod)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ایجاد روش ارسال: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show delivery method details
     */
    public function show(int $id): JsonResponse
    {
        $deliveryMethod = DeliveryMethod::find($id);

        if (!$deliveryMethod) {
            return response()->json([
                'success' => false,
                'message' => 'روش ارسال یافت نشد'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new DeliveryMethodResource($deliveryMethod)
        ]);
    }

    /**
     * Update delivery method
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'estimated_days' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        try {
            $deliveryMethod = DeliveryMethod::find($id);

            if (!$deliveryMethod) {
                return response()->json([
                    'success' => false,
                    'message' => 'روش ارسال یافت نشد'
                ], 404);
            }

            $deliveryMethod->update($request->only([
                'name', 'description', 'price', 'estimated_days', 'is_active', 'sort_order'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'روش ارسال با موفقیت به‌روزرسانی شد',
                'data' => new DeliveryMethodResource($deliveryMethod)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در به‌روزرسانی روش ارسال: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete delivery method
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deliveryMethod = DeliveryMethod::find($id);

            if (!$deliveryMethod) {
                return response()->json([
                    'success' => false,
                    'message' => 'روش ارسال یافت نشد'
                ], 404);
            }

            $deliveryMethod->delete();

            return response()->json([
                'success' => true,
                'message' => 'روش ارسال با موفقیت حذف شد'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف روش ارسال: ' . $e->getMessage()
            ], 500);
        }
    }
}

