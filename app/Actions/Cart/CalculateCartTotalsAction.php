<?php

namespace App\Actions\Cart;

use App\Actions\BaseAction;
use App\Services\CampaignService;
use App\Models\Product;
use App\Models\ProductVariant;

class CalculateCartTotalsAction extends BaseAction
{
    public function __construct(
        protected CampaignService $campaignService
    ) {}

    /**
     * Calculate cart totals
     *
     * @param array $cart
     * @return array [
     *   'items' => array,
     *   'subtotal' => int,
     *   'total_items' => int,
     *   'original_total' => int,
     *   'campaign_discount' => int,
     * ]
     */
    public function execute(...$params): array
    {
        [$cart] = $params;

        $items = [];
        $subtotal = 0;
        $originalTotal = 0;
        $campaignDiscount = 0;
        $totalItems = 0;

        foreach ($cart as $cartKey => $item) {
            $product = Product::with(['campaigns' => function ($query) {
                $query->where('is_active', true)
                    ->where('starts_at', '<=', now())
                    ->where('ends_at', '>=', now())
                    ->orderBy('priority', 'desc');
            }])->find($item['product_id']);

            if (!$product || !$product->is_active) {
                continue;
            }

            $quantity = $item['quantity'];
            $originalPrice = $item['price'];

            // Calculate campaign price
            $variant = null;
            if (isset($item['color_id']) || isset($item['size_id'])) {
                $variant = ProductVariant::where('product_id', $product->id)
                    ->when($item['color_id'] ?? null, fn($q, $v) => $q->where('color_id', $v))
                    ->when($item['size_id'] ?? null, fn($q, $v) => $q->where('size_id', $v))
                    ->first();
            }

            if ($variant) {
                $campaignData = $this->campaignService->calculateVariantPrice($variant);
            } else {
                $campaignData = $this->campaignService->calculateProductPrice($product);
            }

            $finalPrice = $campaignData['has_discount'] 
                ? $campaignData['campaign_price'] 
                : $originalPrice;

            $itemDiscount = $campaignData['has_discount'] 
                ? $campaignData['discount_amount'] * $quantity 
                : 0;

            $lineTotal = $finalPrice * $quantity;
            $originalLineTotal = $originalPrice * $quantity;

            $items[] = [
                'cart_key' => $cartKey,
                'product' => $product,
                'quantity' => $quantity,
                'original_price' => $originalPrice,
                'final_price' => $finalPrice,
                'line_total' => $lineTotal,
                'discount_amount' => $itemDiscount,
                'campaign' => $campaignData['campaign'] ?? null,
            ];

            $subtotal += $lineTotal;
            $originalTotal += $originalLineTotal;
            $campaignDiscount += $itemDiscount;
            $totalItems += $quantity;
        }

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'total_items' => $totalItems,
            'original_total' => $originalTotal,
            'campaign_discount' => $campaignDiscount,
        ];
    }
}

