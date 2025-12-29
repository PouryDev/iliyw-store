<?php

namespace App\Actions\Order;

use App\Actions\BaseAction;
use App\Services\CampaignService;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\DeliveryMethod;

class CalculateOrderTotalsAction extends BaseAction
{
    public function __construct(
        protected CampaignService $campaignService
    ) {}

    /**
     * Calculate order totals from cart
     *
     * @param array $cart
     * @param int $deliveryMethodId
     * @return array [
     *   'items' => array,
     *   'total_amount' => int,
     *   'original_amount' => int,
     *   'campaign_discount' => int,
     *   'delivery_fee' => int,
     * ]
     */
    public function execute(...$params): array
    {
        [$cart, $deliveryMethodId] = $params;

        $deliveryMethod = DeliveryMethod::findOrFail($deliveryMethodId);
        $deliveryFee = $deliveryMethod->fee;

        $items = [];
        $totalAmount = 0;
        $originalTotal = 0;
        $campaignDiscount = 0;

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

            $quantity = (int) $item['quantity'];
            if ($quantity <= 0) continue;

            // Get variant if applicable
            $productVariant = null;
            $originalPrice = (int) $product->price;
            $variantDisplayName = null;

            if (!empty($item['color_id']) || !empty($item['size_id'])) {
                $productVariant = ProductVariant::where('product_id', $product->id)
                    ->when($item['color_id'] ?? null, fn($q, $v) => $q->where('color_id', $v))
                    ->when($item['size_id'] ?? null, fn($q, $v) => $q->where('size_id', $v))
                    ->first();

                if ($productVariant) {
                    $originalPrice = $productVariant->price ?? $product->price;
                    $variantDisplayName = $productVariant->display_name;
                }
            }

            // Calculate campaign discount
            $campaignData = $productVariant 
                ? $this->campaignService->calculateVariantPrice($productVariant)
                : $this->campaignService->calculateProductPrice($product);

            $unitPrice = $campaignData['has_discount'] 
                ? $campaignData['campaign_price'] 
                : $originalPrice;

            $campaignDiscountAmount = $campaignData['has_discount'] 
                ? $campaignData['discount_amount'] 
                : 0;

            $campaignId = $campaignData['campaign']->id ?? null;

            $lineTotal = $unitPrice * $quantity;
            $totalAmount += $lineTotal;
            $originalTotal += $originalPrice * $quantity;
            $campaignDiscount += $campaignDiscountAmount * $quantity;

            $items[] = [
                'product_id' => $product->id,
                'product_variant_id' => $productVariant?->id,
                'color_id' => $item['color_id'] ?? null,
                'size_id' => $item['size_id'] ?? null,
                'variant_display_name' => $variantDisplayName,
                'unit_price' => $unitPrice,
                'original_price' => $originalPrice,
                'campaign_discount_amount' => $campaignDiscountAmount,
                'campaign_id' => $campaignId,
                'quantity' => $quantity,
                'line_total' => $lineTotal,
                'cart_key' => $cartKey,
            ];
        }

        return [
            'items' => $items,
            'total_amount' => $totalAmount,
            'original_amount' => $originalTotal,
            'campaign_discount' => $campaignDiscount,
            'delivery_fee' => $deliveryFee,
        ];
    }
}

