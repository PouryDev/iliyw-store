<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    /**
     * Create a new exception for insufficient stock
     *
     * @param string $productName
     * @param int $requested
     * @param int $available
     * @return static
     */
    public static function create(string $productName, int $requested, int $available): static
    {
        return new static(
            "موجودی کافی برای محصول '{$productName}' وجود ندارد. درخواستی: {$requested}، موجود: {$available}"
        );
    }

    /**
     * Create a new exception with simple message
     *
     * @param string $message
     * @return static
     */
    public static function simple(string $message = 'موجودی کافی وجود ندارد'): static
    {
        return new static($message);
    }
}

