<?php

namespace App\Repositories\Eloquent;

use App\Models\Campaign;
use App\Repositories\Contracts\CampaignRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CampaignRepository extends BaseRepository implements CampaignRepositoryInterface
{
    public function __construct(Campaign $model)
    {
        $this->model = $model;
    }

    /**
     * Get active campaigns
     */
    public function getActive(array $relations = []): Collection
    {
        return $this->model->with($relations)
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->orderBy('priority', 'desc')
            ->get();
    }

    /**
     * Get campaigns for product
     */
    public function getForProduct(int $productId): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->whereHas('products', function ($query) use ($productId) {
                $query->where('products.id', $productId);
            })
            ->orderBy('priority', 'desc')
            ->get();
    }

    /**
     * Get best campaign for product
     */
    public function getBestForProduct(int $productId): ?Campaign
    {
        return $this->getForProduct($productId)->first();
    }

    /**
     * Check if campaign is active now
     */
    public function isActive(int $campaignId): bool
    {
        return $this->model->where('id', $campaignId)
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->exists();
    }

    /**
     * Toggle campaign active status
     */
    public function toggleActive(int $campaignId): bool
    {
        $campaign = $this->findOrFail($campaignId);
        return $campaign->update(['is_active' => !$campaign->is_active]);
    }
}

