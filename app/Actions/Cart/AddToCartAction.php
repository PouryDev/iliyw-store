<?php

namespace App\Actions\Cart;

use App\Actions\BaseAction;
use App\DTOs\CartItemData;
use App\Exceptions\CartException;
use App\Exceptions\InsufficientStockException;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Models\ProductVariant;

class AddToCartAction extends BaseAction
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    /**
     * Add product to cart
     *
     * @param int $productId
     * @param int $quantity
     * @param int|null $colorId
     * @param int|null $sizeId
     * @param array $currentCart
     * @return array Updated cart
     * @throws CartException
     * @throws InsufficientStockException
     */
    public function execute(...$params): array
    {
        [$productId, $quantity, $colorId, $sizeId, $currentCart] = $params;

        // Find product
        $product = $this->productRepository->find($productId, ['*'], ['images']);
        
        if (!$product || !$product->is_active) {
            throw CartException::productNotFound();
        }

        // Generate cart key
        $cartKey = $this->generateCartKey($productId, $colorId, $sizeId);

        // Check stock availability
        $availableStock = $this->getAvailableStock($product, $colorId, $sizeId);
        $currentQuantity = $currentCart[$cartKey]['quantity'] ?? 0;
        $newQuantity = $currentQuantity + $quantity;

        if ($newQuantity > $availableStock) {
            throw InsufficientStockException::create(
                $product->title,
                $newQuantity,
                $availableStock
            );
        }

        // Get price
        $price = $this->getPrice($product, $colorId, $sizeId);

        // Get variant display name
        $variantDisplayName = $this->getVariantDisplayName($colorId, $sizeId);

        // Add/Update cart item
        $currentCart[$cartKey] = [
            'product_id' => $productId,
            'quantity' => $newQuantity,
            'color_id' => $colorId,
            'size_id' => $sizeId,
            'price' => $price,
            'title' => $product->title,
            'image' => $product->images->first()?->path,
            'variant_display_name' => $variantDisplayName,
        ];

        return $currentCart;
    }

    /**
     * Generate unique cart key
     */
    protected function generateCartKey(int $productId, ?int $colorId, ?int $sizeId): string
    {
        return implode('_', array_filter([$productId, $colorId, $sizeId]));
    }

    /**
     * Get available stock
     */
    protected function getAvailableStock($product, ?int $colorId, ?int $sizeId): int
    {
        if ($colorId || $sizeId) {
            $variant = ProductVariant::where('product_id', $product->id)
                ->when($colorId, fn($q) => $q->where('color_id', $colorId))
                ->when($sizeId, fn($q) => $q->where('size_id', $sizeId))
                ->where('is_active', true)
                ->first();

            if (!$variant) {
                throw CartException::variantNotFound();
            }

            return $variant->stock;
        }

        return $product->stock;
    }

    /**
     * Get price for product or variant
     */
    protected function getPrice($product, ?int $colorId, ?int $sizeId): int
    {
        if ($colorId || $sizeId) {
            $variant = ProductVariant::where('product_id', $product->id)
                ->when($colorId, fn($q) => $q->where('color_id', $colorId))
                ->when($sizeId, fn($q) => $q->where('size_id', $sizeId))
                ->where('is_active', true)
                ->first();

            return $variant?->price ?? $product->price;
        }

        return $product->price;
    }

    /**
     * Get variant display name
     */
    protected function getVariantDisplayName(?int $colorId, ?int $sizeId): ?string
    {
        if (!$colorId && !$sizeId) {
            return null;
        }

        $parts = [];

        if ($colorId) {
            $color = \App\Models\Color::find($colorId);
            if ($color) {
                $parts[] = $color->name;
            }
        }

        if ($sizeId) {
            $size = \App\Models\Size::find($sizeId);
            if ($size) {
                $parts[] = $size->name;
            }
        }

        return implode(' - ', $parts);
    }
}

