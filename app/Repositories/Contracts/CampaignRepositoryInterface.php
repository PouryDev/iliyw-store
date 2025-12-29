<?php

namespace App\Repositories\Contracts;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Collection;

interface CampaignRepositoryInterface extends RepositoryInterface
{
    /**
     * Get active campaigns
     *
     * @param array $relations
     * @return Collection
     */
    public function getActive(array $relations = []): Collection;

    /**
     * Get campaigns for product
     *
     * @param int $productId
     * @return Collection
     */
    public function getForProduct(int $productId): Collection;

    /**
     * Get best campaign for product
     *
     * @param int $productId
     * @return Campaign|null
     */
    public function getBestForProduct(int $productId): ?Campaign;

    /**
     * Check if campaign is active now
     *
     * @param int $campaignId
     * @return bool
     */
    public function isActive(int $campaignId): bool;

    /**
     * Toggle campaign active status
     *
     * @param int $campaignId
     * @return bool
     */
    public function toggleActive(int $campaignId): bool;
}

