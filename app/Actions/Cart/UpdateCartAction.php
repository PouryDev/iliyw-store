<?php

namespace App\Actions\Cart;

use App\Actions\BaseAction;
use App\Exceptions\CartException;
use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\ProductVariant;

class UpdateCartAction extends BaseAction
{
    /**
     * Update cart item quantity
     *
     * @param string $cartKey
     * @param int $quantity
     * @param array $currentCart
     * @return array Updated cart
     * @throws CartException
     * @throws InsufficientStockException
     */
    public function execute(...$params): array
    {
        [$cartKey, $quantity, $currentCart] = $params;

        if (!isset($currentCart[$cartKey])) {
            throw CartException::itemNotFound();
        }

        if ($quantity <= 0) {
            throw CartException::invalidQuantity();
        }

        $item = $currentCart[$cartKey];
        
        // Check stock availability
        $product = Product::find($item['product_id']);
        
        if (!$product || !$product->is_active) {
            throw CartException::productNotFound();
        }

        $availableStock = $this->getAvailableStock(
            $product,
            $item['color_id'] ?? null,
            $item['size_id'] ?? null
        );

        if ($quantity > $availableStock) {
            throw InsufficientStockException::create(
                $product->title,
                $quantity,
                $availableStock
            );
        }

        // Update quantity
        $currentCart[$cartKey]['quantity'] = $quantity;

        return $currentCart;
    }

    /**
     * Get available stock
     */
    protected function getAvailableStock(Product $product, ?int $colorId, ?int $sizeId): int
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
}

