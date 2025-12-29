<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class ProductData extends BaseDTO
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?int $categoryId = null,
        public readonly ?string $title = null,
        public readonly ?string $slug = null,
        public readonly ?string $description = null,
        public readonly ?int $price = null,
        public readonly ?int $stock = null,
        public readonly ?bool $hasVariants = null,
        public readonly ?bool $hasColors = null,
        public readonly ?bool $hasSizes = null,
        public readonly ?bool $isActive = null,
    ) {}

    /**
     * Create from request
     *
     * @param Request $request
     * @return self
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            categoryId: $request->input('category_id'),
            title: $request->input('title'),
            slug: $request->input('slug'),
            description: $request->input('description'),
            price: $request->input('price'),
            stock: $request->input('stock'),
            hasVariants: $request->boolean('has_variants'),
            hasColors: $request->boolean('has_colors'),
            hasSizes: $request->boolean('has_sizes'),
            isActive: $request->boolean('is_active', true),
        );
    }
}

