<?php

namespace App\Services\Payment;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Services\Payment\PaymentGatewayFactory;
use App\Services\CampaignService;
use App\Services\DiscountCodeService;
use App\Services\Telegram\Client as TelegramClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;

class PaymentService
{
    /**
     * Initiate payment for an invoice
     *
     * @param Invoice $invoice
     * @param int $gatewayId
     * @param array $additionalData
     * @return array
     */
    public function initiatePayment(Invoice $invoice, int $gatewayId, array $additionalData = []): array
    {
        try {
            $gateway = PaymentGateway::findOrFail($gatewayId);

            if (!$gateway->is_active) {
                return [
                    'success' => false,
                    'message' => 'این درگاه پرداخت فعال نیست',
                ];
            }

            $gatewayInstance = PaymentGatewayFactory::create($gateway);

            if (!$gatewayInstance->isAvailable()) {
                return [
                    'success' => false,
                    'message' => 'درگاه پرداخت در دسترس نیست',
                ];
            }

            // Create transaction record
            $transaction = Transaction::create([
                'invoice_id' => $invoice->id,
                'gateway_id' => $gateway->id,
                'method' => $gateway->type,
                'amount' => $invoice->amount,
                'status' => 'pending',
            ]);

            // Update invoice with gateway
            $invoice->update([
                'payment_gateway_id' => $gateway->id,
            ]);

            // Initiate payment with gateway
            $result = $gatewayInstance->initiate($invoice, array_merge($additionalData, [
                'transaction_id' => $transaction->id,
            ]));

            if ($result['success']) {
                // Update transaction with gateway transaction ID if provided
                // Support both 'authority' (ZarinPal) and 'trackId' (Zibal)
                if (isset($result['form_data']['authority'])) {
                    $transaction->update([
                        'gateway_transaction_id' => $result['form_data']['authority'],
                    ]);
                } elseif (isset($result['form_data']['trackId'])) {
                    $transaction->update([
                        'gateway_transaction_id' => $result['form_data']['trackId'],
                    ]);
                }

                return [
                    'success' => true,
                    'transaction_id' => $transaction->id,
                    'redirect_url' => $result['redirect_url'],
                    'form_data' => $result['form_data'],
                    'message' => $result['message'],
                ];
            } else {
                // Mark transaction as failed
                $transaction->update([
                    'status' => 'rejected',
                ]);

                return [
                    'success' => false,
                    'message' => $result['message'],
                ];
            }
        } catch (\Exception $e) {
            Log::error('Payment initiation error', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id,
                'gateway_id' => $gatewayId,
            ]);

            return [
                'success' => false,
                'message' => 'خطا در شروع پرداخت: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify payment transaction
     *
     * @param Transaction $transaction
     * @param array $callbackData
     * @return array
     */
    public function verifyPayment(Transaction $transaction, array $callbackData = []): array
    {
        try {
            if (!$transaction->gateway_id) {
                return [
                    'success' => false,
                    'verified' => false,
                    'message' => 'درگاه پرداخت برای این تراکنش مشخص نشده است',
                ];
            }

            $gateway = PaymentGateway::findOrFail($transaction->gateway_id);
            $gatewayInstance = PaymentGatewayFactory::create($gateway);

            // Verify payment
            $result = $gatewayInstance->verify($transaction, $callbackData);

            if ($result['verified']) {
                $invoice = $transaction->invoice;
                
                // Get order data from cache (with fallback to session)
                $orderData = Cache::get("pending_order_{$invoice->id}");
                if (!$orderData && Session::has("pending_order_{$invoice->id}")) {
                    $orderData = Session::get("pending_order_{$invoice->id}");
                }

                if (!$orderData) {
                    Log::error('Order data not found for invoice', [
                        'invoice_id' => $invoice->id,
                        'transaction_id' => $transaction->id,
                    ]);
                    
                    return [
                        'success' => false,
                        'verified' => false,
                        'message' => 'داده‌های سفارش یافت نشد',
                    ];
                }

                DB::transaction(function () use ($transaction, $result, $invoice, $orderData) {
                    // Mark transaction as verified
                    $transaction->markAsVerified();

                    // Update transaction with callback data
                    $transaction->update([
                        'callback_data' => $result['data'],
                        'reference' => $result['data']['ref_id'] ?? null,
                    ]);

                    // Create Order after payment verification
                    $order = Order::create([
                        'user_id' => $orderData['user_id'],
                        'customer_name' => $orderData['customer_name'],
                        'customer_phone' => $orderData['customer_phone'],
                        'customer_address' => $orderData['customer_address'],
                        'delivery_method_id' => $orderData['delivery_method_id'],
                        'delivery_address_id' => $orderData['delivery_address_id'] ?? null,
                        'delivery_fee' => $orderData['delivery_fee'],
                        'total_amount' => $orderData['total_amount'],
                        'original_amount' => $orderData['original_amount'],
                        'campaign_discount_amount' => $orderData['campaign_discount_amount'],
                        'discount_code' => $orderData['discount_code'],
                        'discount_amount' => $orderData['discount_amount'],
                        'final_amount' => $orderData['final_amount'],
                        'status' => 'confirmed', // Order is confirmed after payment verification
                        'receipt_path' => $orderData['receipt_path'],
                    ]);

                    // Link Order to Invoice
                    $invoice->update([
                        'order_id' => $order->id,
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);

                    // Create OrderItems and reduce stock
                    $campaignService = new CampaignService();
                    $cart = $orderData['cart'] ?? [];
                    
                    foreach ($orderData['items'] as $itemData) {
                        // Create OrderItem
                        $orderItem = OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $itemData['product_id'],
                            'product_variant_id' => $itemData['product_variant_id'] ?? null,
                            'color_id' => $itemData['color_id'],
                            'size_id' => $itemData['size_id'],
                            'variant_display_name' => $itemData['variant_display_name'] ?? null,
                            'campaign_id' => $itemData['campaign_id'] ?? null,
                            'original_price' => $itemData['original_price'],
                            'campaign_discount_amount' => $itemData['campaign_discount_amount'],
                            'unit_price' => $itemData['unit_price'],
                            'quantity' => $itemData['quantity'],
                            'line_total' => $itemData['line_total'],
                        ]);

                        // Record campaign sale if applicable
                        if ($itemData['campaign_id']) {
                            $campaignService->recordCampaignSale($orderItem);
                        }

                        // Reduce stock based on variant selection
                        $cartKey = $itemData['cart_key'] ?? null;
                        $cartItem = $cart[$cartKey] ?? null;
                        
                        if ($cartItem) {
                            $product = Product::find($itemData['product_id']);
                            $quantity = $itemData['quantity'];
                            
                            if ($cartItem['color_id'] || $cartItem['size_id']) {
                                // Find and reduce variant stock
                                $variant = ProductVariant::where('product_id', $product->id)
                                    ->when($cartItem['color_id'], function ($query) use ($cartItem) {
                                        $query->where('color_id', $cartItem['color_id']);
                                    })
                                    ->when($cartItem['size_id'], function ($query) use ($cartItem) {
                                        $query->where('size_id', $cartItem['size_id']);
                                    })
                                    ->first();
                                
                                if ($variant) {
                                    $variant->decrement('stock', $quantity);
                                }
                            } else {
                                // Reduce main product stock
                                $product->decrement('stock', $quantity);
                            }
                        }
                    }

                    // Apply discount code if provided
                    if (!empty($orderData['discount_code']) && $orderData['discount_amount'] > 0) {
                        $discountService = new DiscountCodeService();
                        $discountCode = \App\Models\DiscountCode::where('code', $orderData['discount_code'])->first();
                        
                        if ($discountCode) {
                            $discountService->applyDiscountToOrder(
                                $order,
                                $discountCode,
                                $orderData['discount_amount']
                            );
                        }
                    }
                });

                // Clear order data from cache and session
                Cache::forget("pending_order_{$invoice->id}");
                Session::forget("pending_order_{$invoice->id}");
                
                // Clear cart after successful payment verification and order creation
                Session::forget('cart');

                // Send Telegram notification to admins
                try {
                    $order = $invoice->fresh()->order;
                    if ($order) {
                        $this->sendOrderNotification($order);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to send order notification', [
                        'error' => $e->getMessage(),
                        'invoice_id' => $invoice->id,
                    ]);
                }

                return [
                    'success' => true,
                    'verified' => true,
                    'message' => 'پرداخت با موفقیت تایید شد و سفارش ثبت شد',
                    'invoice_id' => $transaction->invoice_id,
                ];
            } else {
                // Payment verification failed - mark transaction as rejected
                $transaction->markAsRejected();
                
                // Mark invoice as cancelled if it doesn't have an order yet
                $invoice = $transaction->invoice;
                if (!$invoice->order_id) {
                    $invoice->update([
                        'status' => 'cancelled',
                    ]);
                    
                    // Clean up pending order data from cache and session
                    Cache::forget("pending_order_{$invoice->id}");
                    Session::forget("pending_order_{$invoice->id}");
                }

                return [
                    'success' => false,
                    'verified' => false,
                    'message' => $result['message'],
                ];
            }
        } catch (\Exception $e) {
            Log::error('Payment verification error', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id,
            ]);

            return [
                'success' => false,
                'verified' => false,
                'message' => 'خطا در تایید پرداخت: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle callback from gateway
     *
     * @param string $gatewayType
     * @param array $callbackData
     * @return array
     */
    public function handleCallback(string $gatewayType, array $callbackData): array
    {
        try {
            $gateway = PaymentGateway::where('type', $gatewayType)->first();

            if (!$gateway) {
                return [
                    'success' => false,
                    'message' => 'درگاه پرداخت یافت نشد',
                ];
            }

            $gatewayInstance = PaymentGatewayFactory::create($gateway);
            $result = $gatewayInstance->callback($callbackData);

            if ($result['success'] && $result['transaction_id']) {
                $transaction = Transaction::findOrFail($result['transaction_id']);

                // Update transaction with callback data
                $transaction->update([
                    'callback_data' => $callbackData,
                ]);

                // Verify payment
                return $this->verifyPayment($transaction, $callbackData);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Payment callback error', [
                'error' => $e->getMessage(),
                'gateway_type' => $gatewayType,
                'callback_data' => $callbackData,
            ]);

            return [
                'success' => false,
                'message' => 'خطا در پردازش callback: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get active payment gateways
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveGateways()
    {
        return PaymentGateway::active()->ordered()->get();
    }

    /**
     * Send Telegram notification when a new order is created
     */
    private function sendOrderNotification(Order $order): void
    {
        $adminChatId = config('telegram.admin_chat_id');
        
        if (!$adminChatId) {
            return;
        }

        try {
            // Load order relationships for message formatting
            $order->load(['items.product', 'invoice']);
            
            // Format message in Persian
            $itemsCount = $order->items->count();
            $totalAmount = number_format($order->total_amount) . ' تومان';
            $invoiceNumber = $order->invoice->invoice_number ?? 'N/A';
            
            $message = "🛒 سفارش جدید ثبت شد\n\n";
            $message .= "📋 شماره سفارش: #{$order->id}\n";
            $message .= "🧾 شماره فاکتور: {$invoiceNumber}\n";
            $message .= "👤 نام مشتری: {$order->customer_name}\n";
            $message .= "📞 تلفن: {$order->customer_phone}\n";
            $message .= "📍 آدرس: {$order->customer_address}\n";
            $message .= "📦 تعداد اقلام: {$itemsCount}\n";
            $message .= "💰 مبلغ کل: {$totalAmount}\n";
            $message .= "📊 وضعیت: " . $this->getStatusLabel($order->status) . "\n";
            
            if ($order->receipt_path) {
                $message .= "📎 فایل رسید: دارد\n";
            }

            $telegramClient = new TelegramClient();
            $telegramClient->sendMessage((int) $adminChatId, $message);
        } catch (\Exception $e) {
            // Log error but don't fail order creation
            Log::error('[PaymentService][sendOrderNotification][TELEGRAM] Failed to send order notification', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get Persian label for order status
     */
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'در انتظار',
            'confirmed' => 'تایید شده',
            'shipped' => 'ارسال شده',
            'cancelled' => 'لغو شده',
            default => $status,
        };
    }
}

