<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCampaignRequest;
use App\Http\Resources\CampaignResource;
use App\Repositories\Contracts\CampaignRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CampaignController extends Controller
{
    public function __construct(
        protected CampaignRepositoryInterface $campaignRepository
    ) {}

    /**
     * Get all campaigns
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 20);
        $isActive = $request->input('is_active');

        $query = $this->campaignRepository->newQuery()
            ->with('products')
            ->when($isActive !== null, function ($q) use ($isActive) {
                $q->where('is_active', (bool) $isActive);
            })
            ->latest();

        $campaigns = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => CampaignResource::collection($campaigns->items()),
            'pagination' => [
                'current_page' => $campaigns->currentPage(),
                'last_page' => $campaigns->lastPage(),
                'total' => $campaigns->total(),
            ]
        ]);
    }

    /**
     * Store new campaign
     */
    public function store(StoreCampaignRequest $request): JsonResponse
    {
        try {
            $campaign = $this->campaignRepository->create($request->validated());

            // Attach products if provided
            if ($request->has('product_ids') && is_array($request->product_ids)) {
                $campaign->products()->sync($request->product_ids);
            }

            return response()->json([
                'success' => true,
                'message' => 'کمپین با موفقیت ایجاد شد',
                'data' => new CampaignResource($campaign->fresh('products'))
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ایجاد کمپین: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show campaign details
     */
    public function show(int $id): JsonResponse
    {
        $campaign = $this->campaignRepository->find($id);

        if (!$campaign) {
            return response()->json([
                'success' => false,
                'message' => 'کمپین یافت نشد'
            ], 404);
        }

        $campaign->load('products');

        return response()->json([
            'success' => true,
            'data' => new CampaignResource($campaign)
        ]);
    }

    /**
     * Update campaign
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:percentage,fixed',
            'discount_value' => 'sometimes|numeric|min:0',
            'starts_at' => 'sometimes|date',
            'ends_at' => 'sometimes|date|after:starts_at',
            'is_active' => 'sometimes|boolean',
            'priority' => 'sometimes|integer|min:0',
            'product_ids' => 'sometimes|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        try {
            $updated = $this->campaignRepository->update($id, $request->only([
                'name', 'type', 'discount_value', 'starts_at', 'ends_at', 'is_active', 'priority'
            ]));

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'کمپین یافت نشد'
                ], 404);
            }

            $campaign = $this->campaignRepository->find($id);

            // Update products if provided
            if ($request->has('product_ids') && is_array($request->product_ids)) {
                $campaign->products()->sync($request->product_ids);
            }

            return response()->json([
                'success' => true,
                'message' => 'کمپین با موفقیت به‌روزرسانی شد',
                'data' => new CampaignResource($campaign->fresh('products'))
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در به‌روزرسانی کمپین: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete campaign
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->campaignRepository->delete($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'کمپین یافت نشد'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'کمپین با موفقیت حذف شد'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف کمپین: ' . $e->getMessage()
            ], 500);
        }
    }
}

