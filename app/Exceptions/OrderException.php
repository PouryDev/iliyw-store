<?php

namespace App\Exceptions;

use Exception;

class OrderException extends Exception
{
    /**
     * Create a new exception for empty cart
     *
     * @param string $message
     * @return static
     */
    public static function emptyCart(string $message = 'سبد خرید خالی است'): static
    {
        return new static($message);
    }

    /**
     * Create a new exception for invalid cart
     *
     * @param string $message
     * @return static
     */
    public static function invalidCart(string $message = 'سبد خرید نامعتبر است'): static
    {
        return new static($message);
    }

    /**
     * Create a new exception for order creation failure
     *
     * @param string $message
     * @return static
     */
    public static function creationFailed(string $message = 'خطا در ایجاد سفارش'): static
    {
        return new static($message);
    }

    /**
     * Create a new exception for order not found
     *
     * @param string $message
     * @return static
     */
    public static function notFound(string $message = 'سفارش یافت نشد'): static
    {
        return new static($message);
    }

    /**
     * Create a new exception for cannot cancel order
     *
     * @param string $message
     * @return static
     */
    public static function cannotCancel(string $message = 'امکان لغو سفارش وجود ندارد'): static
    {
        return new static($message);
    }
}

