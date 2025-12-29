<?php

namespace App\Exceptions;

use Exception;

class CartException extends Exception
{
    /**
     * Create a new exception for product not found
     *
     * @param string $message
     * @return static
     */
    public static function productNotFound(string $message = 'محصول یافت نشد'): static
    {
        return new static($message);
    }

    /**
     * Create a new exception for variant not found
     *
     * @param string $message
     * @return static
     */
    public static function variantNotFound(string $message = 'نوع محصول یافت نشد'): static
    {
        return new static($message);
    }

    /**
     * Create a new exception for invalid quantity
     *
     * @param string $message
     * @return static
     */
    public static function invalidQuantity(string $message = 'تعداد نامعتبر است'): static
    {
        return new static($message);
    }

    /**
     * Create a new exception for item not found in cart
     *
     * @param string $message
     * @return static
     */
    public static function itemNotFound(string $message = 'محصول در سبد خرید یافت نشد'): static
    {
        return new static($message);
    }
}

