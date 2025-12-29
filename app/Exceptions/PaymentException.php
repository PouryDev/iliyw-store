<?php

namespace App\Exceptions;

use Exception;

class PaymentException extends Exception
{
    /**
     * Create a new exception for payment initiation failure
     *
     * @param string $message
     * @return static
     */
    public static function initiationFailed(string $message = 'خطا در شروع پرداخت'): static
    {
        return new static($message);
    }

    /**
     * Create a new exception for payment verification failure
     *
     * @param string $message
     * @return static
     */
    public static function verificationFailed(string $message = 'خطا در تایید پرداخت'): static
    {
        return new static($message);
    }

    /**
     * Create a new exception for invalid gateway
     *
     * @param string $message
     * @return static
     */
    public static function invalidGateway(string $message = 'درگاه پرداخت نامعتبر است'): static
    {
        return new static($message);
    }

    /**
     * Create a new exception for gateway unavailable
     *
     * @param string $message
     * @return static
     */
    public static function gatewayUnavailable(string $message = 'درگاه پرداخت در دسترس نیست'): static
    {
        return new static($message);
    }
}

