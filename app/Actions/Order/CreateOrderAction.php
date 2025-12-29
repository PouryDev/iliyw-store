<?php

namespace App\Actions\Order;

use App\Actions\BaseAction;
use App\DTOs\CheckoutData;
use App\Models\Order;
use App\Events\OrderCreated;
use App\Exceptions\OrderException;
use App\Actions\DiscountCode\ValidateDiscountCodeAction;
use App\Actions\DiscountCode\ApplyDiscountCodeAction;
use App\Actions\Campaign\RecordCampaignSaleAction;
use Illuminate\Support\Facades\DB;

class CreateOrderAction extends BaseAction
{
    public function __construct(
        protected CalculateOrderTotalsAction $calculateTotalsAction,
        protected CreateOrderItemsAction $createItemsAction,
        protected ReduceStockAction $reduceStockAction,
        protected ValidateDiscountCodeAction $validateDiscountAction,
        protected ApplyDiscountCodeAction $applyDiscountAction,
        protected RecordCampaignSaleAction $recordCampaignSaleAction
    ) {}

    /**
     * Create order from checkout data
     *
     * @param CheckoutData $checkoutData
     * @return Order
     * @throws OrderException
     */
    public function execute(...$params): Order
    {
        [$checkoutData] = $params;

        if (empty($checkoutData->cart)) {
            throw OrderException::emptyCart();
        }

        return DB::transaction(function () use ($checkoutData) {
            // Calculate totals
            $totals = $this->calculateTotalsAction->execute(
                $checkoutData->cart,
                $checkoutData->deliveryMethodId
            );

            if (empty($totals['items'])) {
                throw OrderException::invalidCart();
            }

            $totalAmount = $totals['total_amount'];
            $originalAmount = $totals['original_amount'];
            $campaignDiscount = $totals['campaign_discount'];
            $deliveryFee = $totals['delivery_fee'];

            // Handle discount code
            $discountAmount = 0;
            $discountCode = null;

            if ($checkoutData->discountCode && $checkoutData->userId) {
                $user = \App\Models\User::find($checkoutData->userId);
                $orderAmount = $totalAmount + $deliveryFee;

                $validation = $this->validateDiscountAction->execute(
                    $checkoutData->discountCode,
                    $user,
                    $orderAmount
                );

                if ($validation['valid']) {
                    $discountCode = $validation['discount_code'];
                    $discountAmount = $this->applyDiscountAction->calculateDiscountAmount(
                        $discountCode,
                        $orderAmount
                    );
                }
            }

            $finalAmount = $totalAmount + $deliveryFee - $discountAmount;

            // Create order
            $order = Order::create([
                'user_id' => $checkoutData->userId,
                'customer_name' => $checkoutData->customerName,
                'customer_phone' => $checkoutData->customerPhone,
                'customer_address' => $checkoutData->customerAddress,
                'delivery_method_id' => $checkoutData->deliveryMethodId,
                'delivery_address_id' => $checkoutData->deliveryAddressId,
                'delivery_fee' => $deliveryFee,
                'total_amount' => $totalAmount,
                'original_amount' => $originalAmount,
                'campaign_discount_amount' => $campaignDiscount,
                'discount_code' => $checkoutData->discountCode,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'status' => 'pending',
                'receipt_path' => $checkoutData->receiptPath,
            ]);

            // Create order items
            $orderItems = $this->createItemsAction->execute($order, $totals['items']);

            // Record campaign sales
            foreach ($orderItems as $orderItem) {
                if ($orderItem->campaign_id) {
                    $this->recordCampaignSaleAction->execute($orderItem);
                }
            }

            // Reduce stock
            $this->reduceStockAction->execute($totals['items'], $checkoutData->cart);

            // Apply discount code
            if ($discountCode && $discountAmount > 0) {
                $this->applyDiscountAction->execute($order, $discountCode, $discountAmount);
            }

            // Dispatch event
            event(new OrderCreated($order));

            return $order->fresh(['items', 'deliveryMethod', 'invoice']);
        });
    }
}

