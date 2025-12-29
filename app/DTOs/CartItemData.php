<?php

namespace App\DTOs;

class CartItemData extends BaseDTO
{
    public function __construct(
        public readonly int $productId,
        public readonly int $quantity,
        public readonly ?int $colorId = null,
        public readonly ?int $sizeId = null,
        public readonly ?int $price = null,
        public readonly ?string $title = null,
        public readonly ?string $image = null,
    ) {}

    /**
     * Create from array
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            productId: $data['product_id'],
            quantity: $data['quantity'],
            colorId: $data['color_id'] ?? null,
            sizeId: $data['size_id'] ?? null,
            price: $data['price'] ?? null,
            title: $data['title'] ?? null,
            image: $data['image'] ?? null,
        );
    }

    /**
     * Generate cart key
     *
     * @return string
     */
    public function getCartKey(): string
    {
        return implode('_', array_filter([
            $this->productId,
            $this->colorId,
            $this->sizeId,
        ]));
    }
}

