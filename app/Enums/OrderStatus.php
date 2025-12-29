<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case SHIPPED = 'shipped';
    case CANCELLED = 'cancelled';

    /**
     * Get the label for the status
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'در انتظار',
            self::CONFIRMED => 'در حال آماده سازی',
            self::SHIPPED => 'ارسال شده',
            self::CANCELLED => 'لغو شده',
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
     * Get all status labels
     *
     * @return array
     */
    public static function labels(): array
    {
        return array_map(fn($status) => $status->label(), self::cases());
    }

    /**
     * Check if status is cancellable
     *
     * @return bool
     */
    public function isCancellable(): bool
    {
        return in_array($this, [self::PENDING, self::CONFIRMED]);
    }

    /**
     * Check if status is final
     *
     * @return bool
     */
    public function isFinal(): bool
    {
        return in_array($this, [self::SHIPPED, self::CANCELLED]);
    }
}

