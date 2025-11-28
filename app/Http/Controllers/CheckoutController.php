<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\DiscountCodeService;
use App\Services\CampaignService;
use App\Services\Telegram\Client as TelegramClient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        $campaignService = new CampaignService();
        
        // Calculate cart totals with campaign discounts
        $cartData = $this->calculateCartTotals($request, $campaignService);
        $total = $cartData['total'];
        $originalTotal = $cartData['original_total'];
        $campaignDiscount = $cartData['campaign_discount'];
        
        // Handle discount code validation
        $discountCode = null;
        $discountAmount = 0;
        $discountCodeError = null;
        
        if ($request->has('discount_code') && $request->filled('discount_code')) {
            if ($request->user()) {
                $discountService = new DiscountCodeService();
                $result = $discountService->validateDiscountCode(
                    $request->input('discount_code'),
                    $request->user(),
                    $total
                );
                
                if ($result['success']) {
                    $discountCode = $result['discount_code'];
                    $discountAmount = $result['discount_amount'];
                } else {
                    $discountCodeError = $result['message'];
                }
            } else {
                $discountCodeError = 'برای استفاده از کد تخفیف باید وارد حساب کاربری خود شوید.';
            }
        }
        
        $finalAmount = $total - $discountAmount;
        
        return view('shop.checkout', compact(
            'cart', 
            'total', 
            'originalTotal',
            'campaignDiscount',
            'discountCode', 
            'discountAmount', 
            'finalAmount',
            'discountCodeError'
        ));
    }

    private function calculateCartTotals(Request $request, CampaignService $campaignService): array
    {
        $cart = $request->session()->get('cart', []);
        $total = 0;
        $originalTotal = 0;
        $campaignDiscount = 0;
        
        foreach ($cart as $cartKey => $qty) {
            // Parse cart key to get product ID and variant info
            $parts = explode('_', $cartKey);
            $productId = $parts[0];
            $colorId = isset($parts[1]) && $parts[1] !== '0' ? $parts[1] : null;
            $sizeId = isset($parts[2]) && $parts[2] !== '0' ? $parts[2] : null;
            
            $product = Product::find($productId);
            if ($product) {
                $unitPrice = $product->price;
                $originalPrice = $product->price;
                $itemCampaignDiscount = 0;
                
                // Find variant info if exists
                if ($colorId || $sizeId) {
                    $productVariant = ProductVariant::where('product_id', $productId)
                        ->when($colorId, function ($query) use ($colorId) {
                            $query->where('color_id', $colorId);
                        })
                        ->when($sizeId, function ($query) use ($sizeId) {
                            $query->where('size_id', $sizeId);
                        })
                        ->first();
                    
                    if ($productVariant) {
                        $originalPrice = $productVariant->price ?? $product->price;
                        
                        // Calculate campaign discount for variant
                        $campaignData = $campaignService->calculateVariantPrice($productVariant);
                        $unitPrice = $campaignData['campaign_price'];
                        $itemCampaignDiscount = $campaignData['discount_amount'];
                    }
                } else {
                    // Calculate campaign discount for product
                    $campaignData = $campaignService->calculateProductPrice($product);
                    $unitPrice = $campaignData['campaign_price'];
                    $itemCampaignDiscount = $campaignData['discount_amount'];
                }
                
                $originalTotal += $originalPrice * $qty;
                $total += $unitPrice * $qty;
                $campaignDiscount += $itemCampaignDiscount * $qty;
            }
        }
        
        return [
            'total' => $total,
            'original_total' => $originalTotal,
            'campaign_discount' => $campaignDiscount,
        ];
    }

    public function place(Request $request)
    {
        try {
            $data = $request->validate([
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|max:50',
                'customer_address' => 'required|string|max:1000',
                'receipt' => 'nullable|image|max:4096', // Receipt is now optional, handled by payment gateway
                'delivery_method_id' => 'required|exists:delivery_methods,id',
                'discount_code' => 'nullable|string|max:50',
                'payment_gateway_id' => 'nullable|exists:payment_gateways,id', // Optional gateway selection
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // For API requests (React), return JSON with validation errors
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'خطا در اعتبارسنجی اطلاعات',
                    'errors' => $e->errors(),
                ], 422);
            }
            // For regular requests, use the default behavior
            throw $e;
        }

        $cart = $request->session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('shop.index');
        }

        $campaignService = new CampaignService();
        
        // Calculate cart totals with campaign discounts
        $cartData = $this->calculateCartTotals($request, $campaignService);
        $total = $cartData['total'];
        $originalTotal = $cartData['original_total'];
        $campaignDiscount = $cartData['campaign_discount'];

        // Get delivery method and calculate delivery fee
        $deliveryMethod = \App\Models\DeliveryMethod::findOrFail($data['delivery_method_id']);
        $deliveryFee = $deliveryMethod->fee;

        // Handle discount code
        $discountAmount = 0;
        $discountCode = null;
        if (!empty($data['discount_code']) && $request->user()) {
            $discountService = new DiscountCodeService();
            $result = $discountService->applyDiscountCode(
                $data['discount_code'],
                $request->user(),
                $total + $deliveryFee  // Include delivery fee in discount calculation
            );
            
            if ($result['success']) {
                $discountAmount = $result['discount_amount'];
                $discountCode = $result['discount_code'];
            } else {
                // For API requests (React), return JSON with validation errors
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'خطا در کد تخفیف',
                        'errors' => ['discount_code' => [$result['message']]],
                    ], 422);
                }
                return back()->withErrors(['discount_code' => $result['message']]);
            }
        }

        $finalAmount = $total + $deliveryFee - $discountAmount;

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        $order = Order::create([
            'user_id' => $request->user() ? $request->user()->id : null,
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'customer_address' => $data['customer_address'],
            'total_amount' => $total,
            'original_amount' => $originalTotal,
            'campaign_discount_amount' => $campaignDiscount,
            'delivery_method_id' => $deliveryMethod->id,
            'delivery_address_id' => $data['delivery_address_id'] ?? null,
            'delivery_fee' => $deliveryFee,
            'discount_code' => $data['discount_code'] ?? null,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'status' => 'pending',
            'receipt_path' => $receiptPath,
        ]);

        foreach ($cart as $cartKey => $qty) {
            // Parse cart key to get product ID and variant info
            $parts = explode('_', $cartKey);
            $productId = $parts[0];
            $colorId = isset($parts[1]) && $parts[1] !== '0' ? $parts[1] : null;
            $sizeId = isset($parts[2]) && $parts[2] !== '0' ? $parts[2] : null;
            
            $product = Product::find($productId);
            
            // Find the variant if color/size is specified
            $productVariant = null;
            $variantDisplayName = null;
            $unitPrice = $product->price;
            
            if ($colorId || $sizeId) {
                $productVariant = ProductVariant::where('product_id', $productId)
                    ->when($colorId, function ($query) use ($colorId) {
                        $query->where('color_id', $colorId);
                    })
                    ->when($sizeId, function ($query) use ($sizeId) {
                        $query->where('size_id', $sizeId);
                    })
                    ->first();
                
                if ($productVariant) {
                    $unitPrice = $productVariant->price ?? $product->price;
                    $variantDisplayName = $productVariant->display_name;
                }
            }
            
            // Apply campaign discount
            $campaignService = new CampaignService();
            $originalPrice = $unitPrice;
            $campaignDiscountAmount = 0;
            $campaignId = null;
            
            if ($productVariant) {
                $campaignData = $campaignService->calculateVariantPrice($productVariant);
            } else {
                $campaignData = $campaignService->calculateProductPrice($product);
            }
            
            if ($campaignData['has_discount']) {
                $unitPrice = $campaignData['campaign_price'];
                $campaignDiscountAmount = $campaignData['discount_amount'];
                $campaignId = $campaignData['campaign']->id;
            }
            
            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_variant_id' => $productVariant?->id,
                'color_id' => $colorId,
                'size_id' => $sizeId,
                'variant_display_name' => $variantDisplayName,
                'campaign_id' => $campaignId,
                'original_price' => $originalPrice,
                'campaign_discount_amount' => $campaignDiscountAmount,
                'unit_price' => $unitPrice,
                'quantity' => $qty,
                'line_total' => $unitPrice * $qty,
            ]);
            
            // Record campaign sale if applicable
            if ($campaignId) {
                $campaignService->recordCampaignSale($orderItem);
            }
        }

        // Apply discount code if provided
        if ($discountCode) {
            $discountService = new DiscountCodeService();
            $discountService->applyDiscountToOrder($order, $discountCode, $discountAmount);
        }

        $invoice = Invoice::create([
            'order_id' => $order->id,
            'payment_gateway_id' => $data['payment_gateway_id'] ?? null,
            'invoice_number' => 'INV-'.Str::upper(Str::random(8)),
            'amount' => $finalAmount,
            'original_amount' => $originalTotal + $deliveryFee,
            'campaign_discount_amount' => $campaignDiscount,
            'discount_code_amount' => $discountAmount,
            'currency' => 'IRR',
            'status' => 'unpaid',
        ]);

        // Send Telegram notification for new order
        $this->sendOrderNotification($order);

        // Don't clear cart here - it will be cleared after successful payment initiation
        // This prevents cart loss if payment gateway is unavailable
        
        // Check if this is an API request (from React)
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'سفارش با موفقیت ثبت شد',
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'amount' => $invoice->amount,
                    'status' => $invoice->status,
                ],
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                ]
            ]);
        }
        
        // For non-API requests, redirect to React SPA
        return redirect()->route('checkout.thanks', $invoice);
    }

    public function thanks(Invoice $invoice)
    {
        return view('shop.thanks', compact('invoice'));
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
            logger()->error('[CheckoutController][sendOrderNotification][TELEGRAM] Failed to send order notification', [
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
            'processing' => 'در حال پردازش',
            'shipped' => 'ارسال شده',
            'delivered' => 'تحویل داده شده',
            'cancelled' => 'لغو شده',
            default => $status,
        };
    }
}


