<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ZarinPal Gateway
        PaymentGateway::updateOrCreate(
            ['type' => 'zarinpal'],
            [
                'name' => 'زرین‌پال',
                'display_name' => 'زرین‌پال',
                'description' => 'درگاه پرداخت آنلاین زرین‌پال',
                'config' => [
                    'merchant_id' => env('ZARINPAL_MERCHANT_ID', ''),
                    'sandbox' => env('ZARINPAL_SANDBOX', false),
                ],
                'is_active' => false, // Default to inactive until configured
                'sort_order' => 1,
            ]
        );

        // Card to Card Gateway
        PaymentGateway::updateOrCreate(
            ['type' => 'card_to_card'],
            [
                'name' => 'کارت به کارت',
                'display_name' => 'پرداخت کارت به کارت',
                'description' => 'پرداخت مستقیم کارت به کارت با آپلود فیش واریزی',
                'config' => [
                    'card_number' => env('PAYMENT_CARD_NUMBER', '6037991553211859'),
                    'card_holder' => env('PAYMENT_CARD_HOLDER', 'مرضیه جعفرنژاد قمی'),
                ],
                'is_active' => true,
                'sort_order' => 2,
            ]
        );
    }
}
