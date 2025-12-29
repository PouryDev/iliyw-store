<?php

namespace App\Actions\Payment;

use App\Actions\BaseAction;
use App\Actions\Order\CreateOrderAction;
use App\Models\Transaction;
use App\Models\Invoice;
use App\Models\PaymentGateway;
use App\Models\DeliveryMethod;
use App\Services\Payment\PaymentGatewayFactory;
use App\Events\PaymentVerified;
use App\Events\PaymentFailed;
use App\Exceptions\PaymentException;
use App\DTOs\CheckoutData;
use App\Enums\TransactionStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class VerifyPaymentAction extends BaseAction
{
    public function __construct(
        protected CreateOrderAction $createOrderAction
    ) {}

    /**
     * Verify payment and create order
     *
     * @param Transaction $transaction
     * @param array $callbackData
     * @return array [
     *   'success' => bool,
     *   'verified' => bool,
     *   'message' => string,
     *   'invoice_id' => int,
     *   'order' => Order|null
     * ]
     * @throws PaymentException
     */
    public function execute(...$params): array
    {
        [$transaction, $callbackData] = $params + [null, []];

        if (!$transaction->gateway_id) {
            throw PaymentException::invalidGateway('درگاه پرداخت برای این تراکنش مشخص نشده است');
        }

        // Early return if transaction is already verified
        if ($transaction->status === TransactionStatus::VERIFIED->value) {
            $invoice = $transaction->invoice;
            
            if ($invoice->order_id) {
                return [
                    'success' => true,
                    'verified' => true,
                    'message' => 'پرداخت قبلاً تایید شده است',
                    'invoice_id' => $transaction->invoice_id,
                    'order' => $invoice->order,
                ];
            }
        }

        $gateway = PaymentGateway::findOrFail($transaction->gateway_id);
        $gatewayInstance = PaymentGatewayFactory::create($gateway);

        // Verify payment with gateway
        $result = $gatewayInstance->verify($transaction, $callbackData);

        if (!$result['verified']) {
            $transaction->update([
                'status' => TransactionStatus::REJECTED->value,
            ]);

            $invoice = $transaction->invoice;
            if (!$invoice->order_id) {
                $invoice->update(['status' => 'cancelled']);
                Cache::forget("pending_order_{$invoice->id}");
                Session::forget("pending_order_{$invoice->id}");
            }

            event(new PaymentFailed($transaction, $result['message']));

            throw PaymentException::verificationFailed($result['message']);
        }

        // Payment verified - create order
        return DB::transaction(function () use ($transaction, $result) {
            $invoice = $transaction->invoice;
            $invoice->refresh();

            // Check if invoice already has an order (idempotency)
            if ($invoice->order_id) {
                return [
                    'success' => true,
                    'verified' => true,
                    'message' => 'پرداخت با موفقیت تایید شد و سفارش قبلاً ثبت شده است',
                    'invoice_id' => $transaction->invoice_id,
                    'order' => $invoice->order,
                ];
            }

            // Get order data from cache
            $orderData = Cache::get("pending_order_{$invoice->id}");
            if (!$orderData && Session::has("pending_order_{$invoice->id}")) {
                $orderData = Session::get("pending_order_{$invoice->id}");
            }

            if (!$orderData) {
                Log::error('Order data not found for invoice', [
                    'invoice_id' => $invoice->id,
                    'transaction_id' => $transaction->id,
                ]);

                throw PaymentException::verificationFailed('داده‌های سفارش یافت نشد');
            }

            // Fallback for missing/invalid delivery method (prevents ModelNotFound)
            $deliveryMethodId = $orderData['delivery_method_id'] ?? null;
            $deliveryMethod = $deliveryMethodId ? DeliveryMethod::find($deliveryMethodId) : null;

            if (!$deliveryMethod) {
                $deliveryMethod = DeliveryMethod::active()->ordered()->first();

                if (!$deliveryMethod) {
                    Log::error('No active delivery method found during payment verification', [
                        'invoice_id' => $invoice->id,
                        'transaction_id' => $transaction->id,
                        'delivery_method_id' => $deliveryMethodId,
                    ]);

                    throw PaymentException::verificationFailed('روش ارسال یافت نشد');
                }

                // Update order data with fallback method and persist to cache/session
                $orderData['delivery_method_id'] = $deliveryMethod->id;
                $orderData['delivery_fee'] = $deliveryMethod->fee;
                Cache::put("pending_order_{$invoice->id}", $orderData, now()->addHours(24));
                Session::put("pending_order_{$invoice->id}", $orderData);

                Log::warning('Delivery method fallback applied during payment verification', [
                    'invoice_id' => $invoice->id,
                    'transaction_id' => $transaction->id,
                    'fallback_delivery_method_id' => $deliveryMethod->id,
                ]);
            }

            // Mark transaction as verified
            $transaction->update([
                'status' => TransactionStatus::VERIFIED->value,
                'verified_at' => now(),
                'callback_data' => $result['data'],
                'reference' => $result['data']['ref_id'] ?? null,
            ]);

            // Create order from order data
            $checkoutData = CheckoutData::fromArray($orderData);
            $order = $this->createOrderAction->execute($checkoutData);

            // Link invoice to order
            $invoice->update([
                'order_id' => $order->id,
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            // Clean up cache
            Cache::forget("pending_order_{$invoice->id}");
            Session::forget("pending_order_{$invoice->id}");
            Session::forget('cart');

            // Dispatch event
            event(new PaymentVerified($transaction, $invoice));

            return [
                'success' => true,
                'verified' => true,
                'message' => 'پرداخت با موفقیت تایید شد و سفارش ثبت شد',
                'invoice_id' => $transaction->invoice_id,
                'order' => $order,
            ];
        });
    }
}

