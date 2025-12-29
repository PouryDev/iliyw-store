<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case PENDING = 'pending';
    case VERIFIED = 'verified';
    case REJECTED = 'rejected';
    case FAILED = 'failed';

    /**
     * Get the label for the status
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'در انتظار',
            self::VERIFIED => 'تایید شده',
            self::REJECTED => 'رد شده',
            self::FAILED => 'ناموفق',
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
     * Check if transaction is successful
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this === self::VERIFIED;
    }

    /**
     * Check if transaction has failed
     *
     * @return bool
     */
    public function hasFailed(): bool
    {
        return in_array($this, [self::REJECTED, self::FAILED]);
    }
}

