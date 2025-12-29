<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDiscountCodeRequest;
use App\Repositories\Contracts\DiscountCodeRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DiscountCodeController extends Controller
{
    public function __construct(
        protected DiscountCodeRepositoryInterface $discountCodeRepository
    ) {}

    /**
     * Get all discount codes
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 20);
        $isActive = $request->input('is_active');

        $query = $this->discountCodeRepository->newQuery()
            ->when($isActive !== null, function ($q) use ($isActive) {
                $q->where('is_active', (bool) $isActive);
            })
            ->latest();

        $discountCodes = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $discountCodes->items(),
            'pagination' => [
                'current_page' => $discountCodes->currentPage(),
                'last_page' => $discountCodes->lastPage(),
                'total' => $discountCodes->total(),
            ]
        ]);
    }

    /**
     * Store new discount code
     */
    public function store(StoreDiscountCodeRequest $request): JsonResponse
    {
        try {
            $discountCode = $this->discountCodeRepository->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'کد تخفیف با موفقیت ایجاد شد',
                'data' => $discountCode
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ایجاد کد تخفیف: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show discount code details
     */
    public function show(int $id): JsonResponse
    {
        $discountCode = $this->discountCodeRepository->find($id);

        if (!$discountCode) {
            return response()->json([
                'success' => false,
                'message' => 'کد تخفیف یافت نشد'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $discountCode
        ]);
    }

    /**
     * Update discount code
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'code' => 'sometimes|string|max:50|unique:discount_codes,code,' . $id,
            'type' => 'sometimes|in:percentage,fixed',
            'value' => 'sometimes|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'is_active' => 'sometimes|boolean',
        ]);

        try {
            $updated = $this->discountCodeRepository->update($id, $request->only([
                'code', 'type', 'value', 'min_order_amount', 'max_discount_amount',
                'usage_limit', 'expires_at', 'is_active'
            ]));

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'کد تخفیف یافت نشد'
                ], 404);
            }

            $discountCode = $this->discountCodeRepository->find($id);

            return response()->json([
                'success' => true,
                'message' => 'کد تخفیف با موفقیت به‌روزرسانی شد',
                'data' => $discountCode
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در به‌روزرسانی کد تخفیف: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete discount code
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->discountCodeRepository->delete($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'کد تخفیف یافت نشد'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'کد تخفیف با موفقیت حذف شد'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف کد تخفیف: ' . $e->getMessage()
            ], 500);
        }
    }
}

