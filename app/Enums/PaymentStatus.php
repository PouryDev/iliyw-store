<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case UNPAID = 'unpaid';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    /**
     * Get the label for the status
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::UNPAID => 'پرداخت نشده',
            self::PAID => 'پرداخت شده',
            self::CANCELLED => 'لغو شده',
            self::REFUNDED => 'مسترد شده',
        };
    }

    /**
     * Get all status values
     *
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if payment is completed
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this === self::PAID;
    }

    /**
     * Check if payment is failed or cancelled
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        return in_array($this, [self::CANCELLED, self::REFUNDED]);
    }
}

