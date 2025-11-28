<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Services\CampaignService;
use App\Services\DiscountCodeService;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Get active payment gateways
     */
    public function gateways()
    {
        $gateways = $this->paymentService->getActiveGateways();

        return response()->json([
            'success' => true,
            'data' => $gateways,
        ]);
    }

    /**
     * Initiate payment
     */
    public function initiate(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'gateway_id' => 'required|exists:payment_gateways,id',
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);

        // Check if invoice belongs to authenticated user
        if ($request->user() && $invoice->order && $invoice->order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'شما دسترسی به این فاکتور ندارید',
            ], 403);
        }

        // Get gateway type for callback URL
        $gateway = PaymentGateway::findOrFail($request->gateway_id);

        $result = $this->paymentService->initiatePayment(
            $invoice,
            $request->gateway_id,
            [
                'callback_url' => route('payment.callback', ['gateway' => $gateway->type]),
            ]
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'data' => [
                    'transaction_id' => $result['transaction_id'],
                    'redirect_url' => $result['redirect_url'],
                    'form_data' => $result['form_data'],
                ],
                'message' => $result['message'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 400);
    }

    /**
     * Handle callback from gateway
     */
    public function callback(Request $request, string $gateway)
    {
        $callbackData = $request->all();

        $result = $this->paymentService->handleCallback($gateway, $callbackData);

        if ($result['success'] && $result['verified']) {
            // Redirect to success page
            $invoice = Invoice::findOrFail($result['invoice_id']);
            return redirect('/thanks/' . urlencode($invoice->invoice_number))
                ->with('success', 'پرداخت با موفقیت انجام شد');
        }

        // Redirect to payment error page with error message
        $errorMessage = $result['message'] ?? 'پرداخت انجام نشد یا توسط کاربر لغو شد';
        return redirect('/payment/error?message=' . urlencode($errorMessage));
    }

    /**
     * Verify payment manually (for card-to-card)
     */
    public function verify(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'receipt' => 'required|image|max:4096',
        ]);

        $transaction = Transaction::findOrFail($request->transaction_id);
        $invoice = $transaction->invoice;
        $gateway = $transaction->gateway;

        // Check if transaction belongs to authenticated user
        if ($request->user() && $invoice->order && $invoice->order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'شما دسترسی به این تراکنش ندارید',
            ], 403);
        }

        // Upload receipt
        $receiptPath = $request->file('receipt')->store('receipts', 'public');

        // Update transaction
        $transaction->update([
            'receipt_path' => $receiptPath,
            'status' => 'pending', // Will be verified by admin
        ]);

        // For card-to-card payments, create order immediately
        if ($gateway && $gateway->type === 'card_to_card') {
            try {
                // Get order data from cache (with fallback to session)
                $orderData = Cache::get("pending_order_{$invoice->id}");
                if (!$orderData && Session::has("pending_order_{$invoice->id}")) {
                    $orderData = Session::get("pending_order_{$invoice->id}");
                }

                if (!$orderData) {
                    Log::error('[PaymentController][verify] Order data not found for invoice', [
                        'invoice_id' => $invoice->id,
                        'transaction_id' => $transaction->id,
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'داده‌های سفارش یافت نشد',
                    ], 400);
                }

                // Update receipt path in order data
                $orderData['receipt_path'] = $receiptPath;

                DB::transaction(function () use ($transaction, $invoice, $orderData) {
                    // Create Order immediately for card-to-card payment
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
                        'status' => 'pending', // Pending admin verification
                        'receipt_path' => $orderData['receipt_path'],
                    ]);

                    Log::info('[PaymentController][verify] Order created for card-to-card payment', [
                        'order_id' => $order->id,
                        'invoice_id' => $invoice->id,
                        'transaction_id' => $transaction->id,
                    ]);

                    // Link Order to Invoice
                    $invoice->update([
                        'order_id' => $order->id,
                        'status' => 'unpaid', // Remains unpaid until admin verifies receipt
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

                // Clear cart after successful order creation
                Session::forget('cart');

                return response()->json([
                    'success' => true,
                    'message' => 'سفارش با موفقیت ثبت شد. پس از تایید پرداخت، سفارش شما پردازش خواهد شد.',
                    'transaction_id' => $transaction->id,
                ]);
            } catch (\Exception $e) {
                Log::error('[PaymentController][verify] Error creating order for card-to-card payment', [
                    'error' => $e->getMessage(),
                    'invoice_id' => $invoice->id,
                    'transaction_id' => $transaction->id,
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'خطا در ثبت سفارش: ' . $e->getMessage(),
                ], 500);
            }
        }

        // For non-card-to-card payments, return success message
        return response()->json([
            'success' => true,
            'message' => 'فیش واریزی با موفقیت آپلود شد. پس از تایید، سفارش شما پردازش خواهد شد.',
            'transaction_id' => $transaction->id,
        ]);
    }

    /**
     * Get payment status
     */
    public function status(Request $request, Transaction $transaction)
    {
        // Check if transaction belongs to authenticated user
        if ($request->user() && $transaction->invoice->order && $transaction->invoice->order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'شما دسترسی به این تراکنش ندارید',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $transaction->id,
                'status' => $transaction->status,
                'verified' => $transaction->isVerified(),
                'invoice' => [
                    'id' => $transaction->invoice->id,
                    'invoice_number' => $transaction->invoice->invoice_number,
                    'status' => $transaction->invoice->status,
                ],
            ],
        ]);
    }
}
