<?php

namespace App\Actions\Campaign;

use App\Actions\BaseAction;
use App\Repositories\Contracts\CampaignRepositoryInterface;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Enums\CampaignType;

class CalculateCampaignPriceAction extends BaseAction
{
    public function __construct(
        protected CampaignRepositoryInterface $campaignRepository
    ) {}

    /**
     * Calculate campaign price for product or variant
     *
     * @param Product|ProductVariant $entity
     * @return array [
     *   'has_discount' => bool,
     *   'original_price' => int,
     *   'campaign_price' => int,
     *   'discount_amount' => int,
     *   'discount_percentage' => float,
     *   'campaign' => Campaign|null
     * ]
     */
    public function execute(...$params): array
    {
        [$entity] = $params;

        $basePrice = $this->getBasePrice($entity);
        
        // Get product for campaign lookup
        $product = $entity instanceof Product ? $entity : $entity->product;
        
        // Get best campaign for this product
        $campaign = $this->campaignRepository->getBestForProduct($product->id);

        if (!$campaign) {
            return [
                'has_discount' => false,
                'original_price' => $basePrice,
                'campaign_price' => $basePrice,
                'discount_amount' => 0,
                'discount_percentage' => 0,
                'campaign' => null,
            ];
        }

        // Calculate discount using enum
        $campaignType = CampaignType::from($campaign->type);
        $discountAmount = $campaignType->calculateDiscount($basePrice, $campaign->discount_value);
        $campaignPrice = $basePrice - $discountAmount;
        
        $discountPercentage = $basePrice > 0 
            ? round(($discountAmount / $basePrice) * 100, 2) 
            : 0;

        return [
            'has_discount' => true,
            'original_price' => $basePrice,
            'campaign_price' => max(0, $campaignPrice),
            'discount_amount' => $discountAmount,
            'discount_percentage' => $discountPercentage,
            'campaign' => $campaign,
        ];
    }

    /**
     * Get base price from entity
     */
    protected function getBasePrice($entity): int
    {
        if ($entity instanceof ProductVariant) {
            return $entity->price ?? $entity->product->price;
        }

        return $entity->price;
    }
}

