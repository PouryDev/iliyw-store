<?php

namespace App\Actions\Order;

use App\Actions\BaseAction;
use App\Models\Order;
use App\Models\OrderItem;

class CreateOrderItemsAction extends BaseAction
{
    /**
     * Create order items from items data
     *
     * @param Order $order
     * @param array $items
     * @return \Illuminate\Support\Collection<OrderItem>
     */
    public function execute(...$params): \Illuminate\Support\Collection
    {
        [$order, $items] = $params;

        $orderItems = collect();

        foreach ($items as $itemData) {
            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $itemData['product_id'],
                'product_variant_id' => $itemData['product_variant_id'] ?? null,
                'color_id' => $itemData['color_id'] ?? null,
                'size_id' => $itemData['size_id'] ?? null,
                'variant_display_name' => $itemData['variant_display_name'] ?? null,
                'campaign_id' => $itemData['campaign_id'] ?? null,
                'original_price' => $itemData['original_price'],
                'campaign_discount_amount' => $itemData['campaign_discount_amount'],
                'unit_price' => $itemData['unit_price'],
                'quantity' => $itemData['quantity'],
                'line_total' => $itemData['line_total'],
            ]);

            $orderItems->push($orderItem);
        }

        return $orderItems;
    }
}

