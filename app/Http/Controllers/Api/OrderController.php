<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\DTOs\CheckoutData;
use App\Actions\Order\CreateOrderAction;
use App\Actions\Order\CalculateOrderTotalsAction;
use App\Actions\Payment\CreateInvoiceAction;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Exceptions\OrderException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected CalculateOrderTotalsAction $calculateOrderTotalsAction,
        protected CreateInvoiceAction $createInvoiceAction
    ) {}

    /**
     * Get user's orders
     */
    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderRepository->getByUserPaginated(
            $request->user()->id,
            10,
            ['items.product.images', 'items.productVariants', 'deliveryMethod', 'invoice']
        );

        return response()->json([
            'success' => true,
            'data' => OrderResource::collection($orders->items()),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total()
            ]
        ]);
    }

    /**
     * Checkout - Create invoice and prepare for payment
     */
    public function checkout(CheckoutRequest $request): JsonResponse
    {
        try {
            // Get cart from session
            $cart = $request->session()->get('cart', []);

            if (empty($cart)) {
                return response()->json([
                    'success' => false,
                    'message' => 'سبد خرید خالی است'
                ], 400);
            }

            // Calculate totals
            $totals = $this->calculateOrderTotalsAction->execute(
                $cart,
                $request->delivery_method_id
            );

            if (empty($totals['items'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'سبد خرید نامعتبر است'
                ], 400);
            }

            $totalAmount = $totals['total_amount'];
            $originalAmount = $totals['original_amount'];
            $campaignDiscount = $totals['campaign_discount'];
            $deliveryFee = $totals['delivery_fee'];

            // Handle discount code (simplified for now - full validation in payment verification)
            $discountAmount = 0;
            // TODO: Add discount code validation

            $finalAmount = $totalAmount + $deliveryFee - $discountAmount;

            // Create invoice
            $invoice = DB::transaction(function () use (
                $request,
                $finalAmount,
                $originalAmount,
                $campaignDiscount,
                $discountAmount,
                $deliveryFee
            ) {
                $checkoutData = CheckoutData::fromRequest($request, []);
                
                return $this->createInvoiceAction->execute(
                    $checkoutData,
                    $finalAmount,
                    $originalAmount + $deliveryFee,
                    $campaignDiscount,
                    $discountAmount
                );
            });

            // Store order data in cache for payment verification
            $orderData = [
                'user_id' => $request->user()?->id,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_address' => $request->customer_address,
                'delivery_method_id' => $request->delivery_method_id,
                'delivery_address_id' => $request->delivery_address_id,
                'delivery_fee' => $deliveryFee,
                'total_amount' => $totalAmount,
                'original_amount' => $originalAmount,
                'campaign_discount_amount' => $campaignDiscount,
                'discount_code' => $request->discount_code,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'receipt_path' => null,
                'items' => $totals['items'],
                'cart' => $cart,
            ];

            // Store in cache (TTL: 24 hours)
            Cache::put("pending_order_{$invoice->id}", $orderData, now()->addHours(24));
            $request->session()->put("pending_order_{$invoice->id}", $orderData);

            return response()->json([
                'success' => true,
                'message' => 'فاکتور ایجاد شد. لطفاً پرداخت را انجام دهید.',
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'amount' => $invoice->amount,
                    'status' => $invoice->status,
                ]
            ]);

        } catch (OrderException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ایجاد سفارش: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order details
     */
    public function show(Request $request, int $orderId): JsonResponse
    {
        try {
            $order = $this->orderRepository->getWithDetails($orderId);

            if ($order->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'دسترسی غیرمجاز'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => new OrderResource($order)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'سفارش یافت نشد'
            ], 404);
        }
    }

    /**
     * Send Telegram notification for order
     */
    public function sendNotification(Request $request): JsonResponse
    {
        $request->validate([
            'invoice_id' => 'nullable|string',
        ]);

        if (!$request->has('invoice_id') || empty($request->invoice_id)) {
            return response()->json([
                'success' => false,
                'message' => 'invoice_id الزامی است'
            ], 400);
        }

        $invoiceIdValue = $request->invoice_id;

        // Find invoice
        $invoice = null;
        if (is_numeric($invoiceIdValue)) {
            $invoice = \App\Models\Invoice::find((int) $invoiceIdValue);
        }

        if (!$invoice) {
            $invoice = \App\Models\Invoice::where('invoice_number', $invoiceIdValue)->first();
        }

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'فاکتور یافت نشد'
            ], 404);
        }

        // Check if already sent
        if ($invoice->telegram_notification_sent_at) {
            return response()->json([
                'success' => true,
                'message' => 'Notification already sent',
                'already_sent' => true,
            ]);
        }

        $order = $invoice->order;

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'سفارش یافت نشد'
            ], 404);
        }

        // Notification is sent via OrderCreated event listener
        // Just mark as sent
        $invoice->update(['telegram_notification_sent_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Notification sent successfully'
        ]);
    }
}
