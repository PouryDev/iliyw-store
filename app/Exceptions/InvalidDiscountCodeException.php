<?php

namespace App\Exceptions;

use Exception;

class InvalidDiscountCodeException extends Exception
{
    /**
     * Create a new exception for invalid discount code
     *
     * @param string $message
     * @return static
     */
    public static function notFound(string $message = 'کد تخفیف یافت نشد'): static
    {
        return new static($message);
    }

    /**
     * Create a new exception for expired discount code
     *
     * @param string $message
     * @return static
     */
    public static function expired(string $message = 'کد تخفیف منقضی شده است'): static
    {
        return new static($message);
    }

    /**
     * Create a new exception for inactive discount code
     *
     * @param string $message
     * @return static
     */
    public static function inactive(string $message = 'کد تخفیف غیرفعال است'): static
    {
        return new static($message);
    }

    /**
     * Create a new exception for usage limit exceeded
     *
     * @param string $message
     * @return static
     */
    public static function usageLimitExceeded(string $message = 'سقف استفاده از کد تخفیف به پایان رسیده است'): static
    {
        return new static($message);
    }

    /**
     * Create a new exception for minimum amount not met
     *
     * @param int $minAmount
     * @return static
     */
    public static function minimumAmountNotMet(int $minAmount): static
    {
        $formattedAmount = number_format($minAmount);
        return new static("حداقل مبلغ سفارش برای استفاده از این کد تخفیف {$formattedAmount} تومان است");
    }

    /**
     * Create a new exception for already used
     *
     * @param string $message
     * @return static
     */
    public static function alreadyUsed(string $message = 'شما قبلاً از این کد تخفیف استفاده کرده‌اید'): static
    {
        return new static($message);
    }
}

