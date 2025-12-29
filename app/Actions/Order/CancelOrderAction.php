<?php

namespace App\Actions\Order;

use App\Actions\BaseAction;
use App\Models\Order;
use App\Events\OrderCancelled;
use App\Exceptions\OrderException;
use App\Enums\OrderStatus;

class CancelOrderAction extends BaseAction
{
    public function __construct(
        protected RestoreStockAction $restoreStockAction
    ) {}

    /**
     * Cancel order and restore stock
     *
     * @param Order $order
     * @return Order
     * @throws OrderException
     */
    public function execute(...$params): Order
    {
        [$order] = $params;

        // Check if order can be cancelled
        $status = OrderStatus::from($order->status);
        
        if (!$status->isCancellable()) {
            throw OrderException::cannotCancel('وضعیت سفارش اجازه لغو را نمی‌دهد');
        }

        // Restore stock
        $this->restoreStockAction->execute($order);

        // Update order status
        $order->update(['status' => OrderStatus::CANCELLED->value]);

        // Dispatch event
        event(new OrderCancelled($order));

        return $order->fresh();
    }
}

