<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Campaign;
use App\Services\Telegram\Client as TelegramClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['items.product.images', 'items.productVariants'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total()
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string|max:500',
            'discount_code' => 'nullable|string|exists:discount_codes,code'
        ]);

        // Create order logic here
        // This would be similar to your existing CheckoutController

        return response()->json([
            'success' => true,
            'message' => 'سفارش با موفقیت ثبت شد'
        ]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string|max:500',
            'delivery_method_id' => 'required|exists:delivery_methods,id',
            'discount_code' => 'nullable|string|exists:discount_codes,code',
            'payment_gateway_id' => 'nullable|exists:payment_gateways,id',
        ]);

        // Get cart from session
        $cart = $request->session()->get('cart', []);
        
        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'سبد خرید خالی است'
            ], 400);
        }

        // Get delivery method and calculate delivery fee
        $deliveryMethod = \App\Models\DeliveryMethod::findOrFail($request->input('delivery_method_id'));
        $deliveryFee = $deliveryMethod->fee;

        // Calculate totals and prepare order items data
        $itemsPayload = [];
        $totalAmount = 0;
        $originalTotal = 0;
        $campaignDiscount = 0;

        $campaignService = new \App\Services\CampaignService();

        foreach ($cart as $key => $item) {
            $product = Product::with(['campaigns' => function ($query) {
                $query->where('is_active', true)
                      ->where('starts_at', '<=', now())
                      ->where('ends_at', '>=', now())
                      ->orderBy('priority', 'desc');
            }])->find($item['product_id'] ?? null);
            if (!$product) {
                continue;
            }
            $quantity = (int) ($item['quantity'] ?? 0);
            if ($quantity <= 0) { continue; }

            // Get variant price if applicable
            $productVariant = null;
            $unitPrice = (int) $product->price;
            $originalPrice = $product->price;
            $campaignDiscountAmount = 0;
            $campaignId = null;
            $variantDisplayName = null;

            if ($item['color_id'] || $item['size_id']) {
                $productVariant = ProductVariant::where('product_id', $product->id)
                    ->when($item['color_id'], function ($query) use ($item) {
                        $query->where('color_id', $item['color_id']);
                    })
                    ->when($item['size_id'], function ($query) use ($item) {
                        $query->where('size_id', $item['size_id']);
                    })
                    ->first();
                    
                if ($productVariant) {
                    $originalPrice = $productVariant->price ?? $product->price;
                    $variantDisplayName = $productVariant->display_name;
                }
            }

            // Calculate campaign discount
            if ($productVariant) {
                $campaignData = $campaignService->calculateVariantPrice($productVariant);
            } else {
                $campaignData = $campaignService->calculateProductPrice($product);
            }

            if ($campaignData['has_discount']) {
                $unitPrice = $campaignData['campaign_price'];
                $campaignDiscountAmount = $campaignData['discount_amount'];
                $campaignId = $campaignData['campaign']->id;
            } else {
                $unitPrice = $originalPrice;
            }

            $lineTotal = $unitPrice * $quantity;
            $totalAmount += $lineTotal;
            $originalTotal += $originalPrice * $quantity;
            $campaignDiscount += $campaignDiscountAmount * $quantity;

            $itemsPayload[] = [
                'product_id' => $product->id,
                'product_variant_id' => $productVariant?->id,
                'color_id' => $item['color_id'] ?? null,
                'size_id' => $item['size_id'] ?? null,
                'variant_display_name' => $variantDisplayName,
                'unit_price' => $unitPrice,
                'original_price' => $originalPrice,
                'campaign_discount_amount' => $campaignDiscountAmount,
                'campaign_id' => $campaignId,
                'quantity' => $quantity,
                'line_total' => $lineTotal,
                'cart_key' => $key,
            ];
        }

        if ($totalAmount <= 0 || count($itemsPayload) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'سبد خرید نامعتبر است'
            ], 400);
        }

        // Handle discount code
        $discountAmount = 0;
        $discountCode = null;
        if (!empty($request->input('discount_code')) && $request->user()) {
            $discountService = new \App\Services\DiscountCodeService();
            $result = $discountService->applyDiscountCode(
                $request->input('discount_code'),
                $request->user(),
                $totalAmount + $deliveryFee
            );
            
            if ($result['success']) {
                $discountAmount = $result['discount_amount'];
                $discountCode = $result['discount_code'];
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => ['discount_code' => [$result['message']]],
                ], 422);
            }
        }

        $finalAmount = $totalAmount + $deliveryFee - $discountAmount;

        // Handle receipt upload
        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        // Create Invoice WITHOUT Order - Order will be created after payment verification
        $invoice = DB::transaction(function () use ($request, $totalAmount, $originalTotal, $campaignDiscount, $deliveryFee, $discountAmount, $finalAmount, $receiptPath) {
            $invoice = Invoice::create([
                'order_id' => null, // Will be set after payment verification
                'payment_gateway_id' => $request->input('payment_gateway_id'),
                'invoice_number' => 'INV-' . Str::upper(Str::random(8)),
                'amount' => $finalAmount,
                'original_amount' => $originalTotal + $deliveryFee,
                'campaign_discount_amount' => $campaignDiscount,
                'discount_code_amount' => $discountAmount,
                'currency' => 'IRR',
                'status' => 'unpaid',
            ]);

            return $invoice;
        });

        // Store order data in cache keyed by invoice_id for later use after payment verification
        // Using cache instead of session because payment callbacks from external gateways
        // might not have access to the session
        // This includes all data needed to create the order
        $orderData = [
            'user_id' => optional($request->user())->id,
            'customer_name' => $request->input('customer_name'),
            'customer_phone' => $request->input('customer_phone'),
            'customer_address' => $request->input('customer_address'),
            'delivery_method_id' => $request->input('delivery_method_id'),
            'delivery_address_id' => $request->input('delivery_address_id'),
            'delivery_fee' => $deliveryFee,
            'total_amount' => $totalAmount,
            'original_amount' => $originalTotal,
            'campaign_discount_amount' => $campaignDiscount,
            'discount_code' => $request->input('discount_code'),
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'receipt_path' => $receiptPath,
            'items' => $itemsPayload,
            'cart' => $cart, // Store cart data for stock reduction
        ];

        // Store order data in cache with invoice_id as key (TTL: 24 hours)
        \Illuminate\Support\Facades\Cache::put("pending_order_{$invoice->id}", $orderData, now()->addHours(24));
        
        // Also store in session as backup (for same-session callbacks)
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
    }

    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $order->load(['items.product', 'items.variant', 'invoice', 'transactions']);

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    /**
     * Send Telegram notification for an order based on invoice
     * This endpoint is called from the Thanks page
     */
    public function sendNotification(Request $request)
    {
        $request->validate([
            'invoice_id' => 'nullable|string',
        ]);

        if (!$request->has('invoice_id') || empty($request->input('invoice_id'))) {
            return response()->json([
                'success' => false,
                'message' => 'invoice_id الزامی است'
            ], 400);
        }

        $invoiceIdValue = $request->input('invoice_id');

        // Search for invoice by id or invoice_number
        // Try as id first (if numeric), then as invoice_number
        $invoice = null;
        if (is_numeric($invoiceIdValue)) {
            $invoice = \App\Models\Invoice::find((int)$invoiceIdValue);
        }
        
        // If not found or not numeric, try as invoice_number
        if (!$invoice) {
            $invoice = \App\Models\Invoice::where('invoice_number', $invoiceIdValue)->first();
        }

        if (!$invoice) {
            logger()->warning('[OrderController][sendNotification] Invoice not found', [
                'invoice_id' => $invoiceIdValue,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'فاکتور یافت نشد'
            ], 404);
        }

        // Check if notification already sent in this session
        $notificationKey = "telegram_notification_sent_{$invoice->id}";
        if ($request->session()->has($notificationKey)) {
            return response()->json([
                'success' => true,
                'message' => 'Notification already sent',
                'already_sent' => true,
            ]);
        }

        // Find order for this invoice
        $order = $invoice->order;
        
        if (!$order) {
            logger()->warning('[OrderController][sendNotification] Order not found for invoice', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'سفارش یافت نشد'
            ], 404);
        }

        // Send notification
        try {
            $this->sendOrderNotification($order);
            
            // Mark as sent in session (expires when session expires)
            $request->session()->put($notificationKey, true);
            
            return response()->json([
                'success' => true,
                'message' => 'Notification sent successfully'
            ]);
        } catch (\Exception $e) {
            logger()->error('[OrderController][sendNotification] Failed to send notification', [
                'order_id' => $order->id,
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'خطا در ارسال notification: ' . $e->getMessage()
            ], 500);
        }
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

            // Build admin order detail URL
            $adminOrderUrl = url('/admin/orders/' . $order->id);

            // Create inline keyboard with order detail button
            $replyMarkup = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => '🔍 مشاهده جزئیات سفارش',
                            'url' => $adminOrderUrl,
                        ],
                    ],
                ],
            ];

            $telegramClient = new TelegramClient();
            $telegramClient->sendMessage((int) $adminChatId, $message, $replyMarkup);
        } catch (\Exception $e) {
            // Log error but don't fail order creation
            logger()->error('[OrderController][sendOrderNotification][TELEGRAM] Failed to send order notification', [
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
