<?php

namespace App\Actions\Order;

use App\Actions\BaseAction;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;

class RestoreStockAction extends BaseAction
{
    /**
     * Restore stock when order is cancelled
     *
     * @param Order $order
     * @return mixed
     */
    public function execute(...$params): mixed
    {
        [$order] = $params;

        $order->load('items');

        foreach ($order->items as $item) {
            $quantity = $item->quantity;

            if ($item->product_variant_id) {
                // Restore variant stock
                $variant = ProductVariant::find($item->product_variant_id);
                if ($variant) {
                    $variant->increment('stock', $quantity);
                }
            } else {
                // Restore product stock
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('stock', $quantity);
                }
            }
        }

        return null;
    }
}

