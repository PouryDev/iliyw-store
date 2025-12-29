<?php

namespace App\Actions\DiscountCode;

use App\Actions\BaseAction;
use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\User;
use App\Models\DiscountCodeUsage;
use App\Enums\DiscountType;

class ApplyDiscountCodeAction extends BaseAction
{
    /**
     * Apply discount code and create usage record
     *
     * @param Order $order
     * @param DiscountCode $discountCode
     * @param int $discountAmount
     * @return DiscountCodeUsage
     */
    public function execute(...$params): DiscountCodeUsage
    {
        [$order, $discountCode, $discountAmount] = $params;

        // Create usage record
        $usage = DiscountCodeUsage::create([
            'discount_code_id' => $discountCode->id,
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'discount_amount' => $discountAmount,
            'order_amount' => $order->total_amount + $order->delivery_fee,
        ]);

        return $usage;
    }

    /**
     * Calculate discount amount
     *
     * @param DiscountCode $discountCode
     * @param int $orderAmount
     * @return int
     */
    public function calculateDiscountAmount(DiscountCode $discountCode, int $orderAmount): int
    {
        $discountType = DiscountType::from($discountCode->type);
        return $discountType->calculateDiscount($orderAmount, $discountCode->value);
    }
}

