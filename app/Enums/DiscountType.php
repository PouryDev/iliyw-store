<?php

namespace App\Enums;

enum DiscountType: string
{
    case PERCENTAGE = 'percentage';
    case FIXED = 'fixed';

    /**
     * Get the label for the type
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::PERCENTAGE => 'درصدی',
            self::FIXED => 'مبلغ ثابت',
        };
    }

    /**
     * Get all type values
     *
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Calculate discount amount
     *
     * @param int $orderAmount
     * @param int $value
     * @return int
     */
    public function calculateDiscount(int $orderAmount, int $value): int
    {
        return match($this) {
            self::PERCENTAGE => (int) ($orderAmount * $value / 100),
            self::FIXED => min($value, $orderAmount),
        };
    }

    /**
     * Calculate final amount
     *
     * @param int $orderAmount
     * @param int $value
     * @return int
     */
    public function calculateFinalAmount(int $orderAmount, int $value): int
    {
        $discount = $this->calculateDiscount($orderAmount, $value);
        return max(0, $orderAmount - $discount);
    }
}

