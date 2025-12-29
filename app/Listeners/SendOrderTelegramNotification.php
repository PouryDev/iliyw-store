<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\Telegram\Client as TelegramClient;
use Illuminate\Support\Facades\Log;

class SendOrderTelegramNotification
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected TelegramClient $telegramClient
    ) {}

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
        $adminChatId = config('telegram.admin_chat_id');

        if (!$adminChatId) {
            Log::warning('[SendOrderTelegramNotification] Telegram admin chat ID not configured');
            return;
        }

        try {
            $order->load(['items.product', 'invoice', 'deliveryMethod']);

            $itemsCount = $order->items->count();
            $totalAmount = number_format($order->total_amount) . ' تومان';
            $finalAmount = number_format($order->final_amount) . ' تومان';
            $invoiceNumber = $order->invoice->invoice_number ?? 'N/A';
            $deliveryMethodTitle = $order->deliveryMethod ? $order->deliveryMethod->title : 'تعیین نشده';

            $message = "🛒 سفارش جدید ثبت شد\n\n";
            $message .= "📋 شماره سفارش: #{$order->id}\n";
            $message .= "🧾 شماره فاکتور: {$invoiceNumber}\n";
            $message .= "👤 نام مشتری: {$order->customer_name}\n";
            $message .= "📞 تلفن: {$order->customer_phone}\n";
            $message .= "📍 آدرس: {$order->customer_address}\n";
            $message .= "📦 تعداد اقلام: {$itemsCount}\n";
            $message .= "💰 مبلغ کل: {$totalAmount}\n";
            $message .= "💳 مبلغ پرداخت شده: {$finalAmount}\n";
            $message .= "🚚 روش ارسال: {$deliveryMethodTitle}\n";
            $message .= "📊 وضعیت: در انتظار\n";

            if ($order->receipt_path) {
                $message .= "📎 فایل رسید: دارد\n";
            }

            $adminOrderUrl = url('/admin/orders/' . $order->id);

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

            $this->telegramClient->sendMessage((int) $adminChatId, $message, $replyMarkup);

            Log::info('[SendOrderTelegramNotification] Notification sent successfully', [
                'order_id' => $order->id,
            ]);
        } catch (\Exception $e) {
            Log::error('[SendOrderTelegramNotification] Failed to send notification', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

