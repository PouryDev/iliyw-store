<?php

namespace App\Actions\Order;

use App\Actions\BaseAction;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Exceptions\InsufficientStockException;

class ReduceStockAction extends BaseAction
{
    /**
     * Reduce stock for order items
     *
     * @param array $items Order items data
     * @param array $cart Original cart data
     * @return void
     * @throws InsufficientStockException
     */
    public function execute(...$params): void
    {
        [$items, $cart] = $params;

        foreach ($items as $itemData) {
            $cartKey = $itemData['cart_key'] ?? null;
            $cartItem = $cart[$cartKey] ?? null;

            if (!$cartItem) {
                continue;
            }

            $product = Product::find($itemData['product_id']);
            if (!$product) {
                continue;
            }

            $quantity = $itemData['quantity'];

            if (!empty($cartItem['color_id']) || !empty($cartItem['size_id'])) {
                // Reduce variant stock
                $variant = ProductVariant::where('product_id', $product->id)
                    ->when($cartItem['color_id'] ?? null, fn($q, $v) => $q->where('color_id', $v))
                    ->when($cartItem['size_id'] ?? null, fn($q, $v) => $q->where('size_id', $v))
                    ->first();

                if ($variant) {
                    if ($variant->stock < $quantity) {
                        throw InsufficientStockException::create(
                            $product->title,
                            $quantity,
                            $variant->stock
                        );
                    }
                    $variant->decrement('stock', $quantity);
                }
            } else {
                // Reduce product stock
                if ($product->stock < $quantity) {
                    throw InsufficientStockException::create(
                        $product->title,
                        $quantity,
                        $product->stock
                    );
                }
                $product->decrement('stock', $quantity);
            }
        }
    }
}

