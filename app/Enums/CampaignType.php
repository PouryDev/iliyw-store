<?php

namespace App\Enums;

enum CampaignType: string
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
     * @param int $basePrice
     * @param int $value
     * @return int
     */
    public function calculateDiscount(int $basePrice, int $value): int
    {
        return match($this) {
            self::PERCENTAGE => (int) ($basePrice * $value / 100),
            self::FIXED => min($value, $basePrice),
        };
    }

    /**
     * Calculate final price
     *
     * @param int $basePrice
     * @param int $value
     * @return int
     */
    public function calculateFinalPrice(int $basePrice, int $value): int
    {
        $discount = $this->calculateDiscount($basePrice, $value);
        return max(0, $basePrice - $discount);
    }
}

