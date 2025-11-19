<?php

namespace App\Services;

use App\Models\DiscountCode;
use App\Models\DiscountCodeUsage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DiscountCodeService
{
    /**
     * Validate and apply discount code to an order
     * Uses database locks to prevent race conditions
     */
    public function applyDiscountCode(string $code, User $user, int $orderAmount): array
    {
        return DB::transaction(function () use ($code, $user, $orderAmount) {
            // Lock the discount code row to prevent race conditions
            $discountCode = DiscountCode::where('code', $code)
                ->lockForUpdate()
                ->first();

            if (!$discountCode) {
                return [
                    'success' => false,
                    'message' => 'کد تخفیف یافت نشد.',
                    'discount_amount' => 0
                ];
            }

            // Check if user has already used this code
            if ($user->hasUsedDiscountCode($code)) {
                return [
                    'success' => false,
                    'message' => 'شما قبلاً از این کد تخفیف استفاده کرده‌اید.',
                    'discount_amount' => 0
                ];
            }

            // Check if code can be used
            if (!$discountCode->canBeUsed()) {
                $message = 'کد تخفیف قابل استفاده نیست.';
                if ($discountCode->isExpired()) {
                    $message = 'کد تخفیف منقضی شده است.';
                } elseif ($discountCode->isUsageLimitReached()) {
                    $message = 'تعداد مجاز استفاده از این کد تخفیف به پایان رسیده است.';
                }
                
                return [
                    'success' => false,
                    'message' => $message,
                    'discount_amount' => 0
                ];
            }

            // Check minimum order amount
            if ($discountCode->min_order_amount && $orderAmount < $discountCode->min_order_amount) {
                return [
                    'success' => false,
                    'message' => "حداقل مبلغ سفارش برای استفاده از این کد تخفیف {$discountCode->min_order_amount} تومان است.",
                    'discount_amount' => 0
                ];
            }

            // Calculate discount amount
            $discountAmount = $discountCode->calculateDiscount($orderAmount);

            if ($discountAmount <= 0) {
                return [
                    'success' => false,
                    'message' => 'کد تخفیف قابل اعمال نیست.',
                    'discount_amount' => 0
                ];
            }

            return [
                'success' => true,
                'message' => 'کد تخفیف با موفقیت اعمال شد.',
                'discount_amount' => $discountAmount,
                'discount_code' => $discountCode
            ];
        });
    }

    /**
     * Apply discount code to order and create usage record
     * This should be called after order is created
     */
    public function applyDiscountToOrder(Order $order, DiscountCode $discountCode, int $discountAmount): void
    {
        DB::transaction(function () use ($order, $discountCode, $discountAmount) {
            // Update order with discount information
            $order->update([
                'discount_code' => $discountCode->code,
                'discount_amount' => $discountAmount,
                'final_amount' => $order->total_amount - $discountAmount
            ]);

            // Create usage record
            DiscountCodeUsage::create([
                'discount_code_id' => $discountCode->id,
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'discount_amount' => $discountAmount
            ]);

            // Increment usage count
            $discountCode->incrementUsage();
        });
    }

    /**
     * Validate discount code without applying it
     */
    public function validateDiscountCode(string $code, User $user, int $orderAmount): array
    {
        $discountCode = DiscountCode::where('code', $code)->first();

        if (!$discountCode) {
            return [
                'success' => false,
                'message' => 'کد تخفیف یافت نشد.',
                'discount_amount' => 0
            ];
        }

        if ($user->hasUsedDiscountCode($code)) {
            return [
                'success' => false,
                'message' => 'شما قبلاً از این کد تخفیف استفاده کرده‌اید.',
                'discount_amount' => 0
            ];
        }

        if (!$discountCode->canBeUsed()) {
            $message = 'کد تخفیف قابل استفاده نیست.';
            if ($discountCode->isExpired()) {
                $message = 'کد تخفیف منقضی شده است.';
            } elseif ($discountCode->isUsageLimitReached()) {
                $message = 'تعداد مجاز استفاده از این کد تخفیف به پایان رسیده است.';
            }
            
            return [
                'success' => false,
                'message' => $message,
                'discount_amount' => 0
            ];
        }

        if ($discountCode->min_order_amount && $orderAmount < $discountCode->min_order_amount) {
            return [
                'success' => false,
                'message' => "حداقل مبلغ سفارش برای استفاده از این کد تخفیف {$discountCode->min_order_amount} تومان است.",
                'discount_amount' => 0
            ];
        }

        $discountAmount = $discountCode->calculateDiscount($orderAmount);

        if ($discountAmount <= 0) {
            return [
                'success' => false,
                'message' => 'کد تخفیف قابل اعمال نیست.',
                'discount_amount' => 0
            ];
        }

        return [
            'success' => true,
            'message' => 'کد تخفیف معتبر است.',
            'discount_amount' => $discountAmount,
            'discount_code' => $discountCode
        ];
    }

    /**
     * Remove discount code from order
     */
    public function removeDiscountFromOrder(Order $order): void
    {
        if (!$order->discount_code) {
            return;
        }

        DB::transaction(function () use ($order) {
            $discountCode = DiscountCode::where('code', $order->discount_code)->first();
            
            if ($discountCode) {
                // Decrement usage count
                $discountCode->decrement('used_count');
                
                // Delete usage record
                DiscountCodeUsage::where('order_id', $order->id)->delete();
            }

            // Update order
            $order->update([
                'discount_code' => null,
                'discount_amount' => 0,
                'final_amount' => $order->total_amount
            ]);
        });
    }

    /**
     * Get discount code statistics
     */
    public function getDiscountCodeStats(DiscountCode $discountCode): array
    {
        $usages = $discountCode->usages()->with('order')->get();
        
        $totalDiscountAmount = $usages->sum('discount_amount');
        $averageDiscountAmount = $usages->count() > 0 ? $totalDiscountAmount / $usages->count() : 0;
        
        return [
            'total_usage' => $usages->count(),
            'total_discount_amount' => $totalDiscountAmount,
            'average_discount_amount' => round($averageDiscountAmount),
            'usage_limit_reached' => $discountCode->isUsageLimitReached(),
            'remaining_usage' => $discountCode->usage_limit ? max(0, $discountCode->usage_limit - $discountCode->used_count) : null,
        ];
    }
}