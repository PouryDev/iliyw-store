<?php

namespace App\Actions\Payment;

use App\Actions\BaseAction;
use App\Models\Invoice;
use App\DTOs\CheckoutData;
use Illuminate\Support\Str;

class CreateInvoiceAction extends BaseAction
{
    /**
     * Create invoice for checkout
     *
     * @param CheckoutData $checkoutData
     * @param int $finalAmount
     * @param int $originalAmount
     * @param int $campaignDiscount
     * @param int $discountAmount
     * @return Invoice
     */
    public function execute(...$params): Invoice
    {
        [$checkoutData, $finalAmount, $originalAmount, $campaignDiscount, $discountAmount] = $params;

        $invoice = Invoice::create([
            'order_id' => null, // Will be set after payment verification
            'payment_gateway_id' => $checkoutData->paymentGatewayId,
            'invoice_number' => 'INV-' . Str::upper(Str::random(8)),
            'amount' => $finalAmount,
            'original_amount' => $originalAmount,
            'campaign_discount_amount' => $campaignDiscount,
            'discount_code_amount' => $discountAmount,
            'currency' => 'IRR',
            'status' => 'unpaid',
        ]);

        return $invoice;
    }
}

