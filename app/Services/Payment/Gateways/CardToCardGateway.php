<?php

namespace App\Services\Payment\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Invoice;
use App\Models\PaymentGateway;
use App\Models\Transaction;

class CardToCardGateway implements PaymentGatewayInterface
{
    protected PaymentGateway $gateway;
    protected string $cardNumber;
    protected string $cardHolder;

    public function __construct(PaymentGateway $gateway)
    {
        $this->gateway = $gateway;
        $this->cardNumber = $gateway->getConfig('card_number', '');
        $this->cardHolder = $gateway->getConfig('card_holder', '');
    }

    /**
     * Initialize card-to-card payment
     * Returns form data instead of redirect URL
     */
    public function initiate(Invoice $invoice, array $additionalData = []): array
    {
        if (empty($this->cardNumber) || empty($this->cardHolder)) {
            return [
                'success' => false,
                'redirect_url' => null,
                'form_data' => null,
                'message' => 'اطلاعات کارت تنظیم نشده است',
            ];
        }

        return [
            'success' => true,
            'redirect_url' => null,
            'form_data' => [
                'card_number' => $this->cardNumber,
                'card_holder' => $this->cardHolder,
                'amount' => $invoice->amount,
                'invoice_number' => $invoice->invoice_number,
            ],
            'message' => 'لطفاً مبلغ را به شماره کارت واریز کرده و فیش را آپلود کنید',
        ];
    }

    /**
     * Verify card-to-card payment
     * For card-to-card, verification is manual (admin verifies receipt)
     */
    public function verify(Transaction $transaction, array $callbackData = []): array
    {
        // For card-to-card, verification is done manually by admin
        // This method can be used to check if receipt is uploaded
        if ($transaction->receipt_path && $transaction->status === 'verified') {
            return [
                'success' => true,
                'verified' => true,
                'message' => 'پرداخت تایید شده است',
                'data' => [],
            ];
        }

        return [
            'success' => true,
            'verified' => false,
            'message' => 'در انتظار تایید پرداخت',
            'data' => [],
        ];
    }

    /**
     * Handle callback for card-to-card
     * For card-to-card, callback happens when user uploads receipt
     */
    public function callback(array $callbackData): array
    {
        // For card-to-card, callback is when receipt is uploaded
        // This is handled in the payment controller
        $transactionId = $callbackData['transaction_id'] ?? null;

        if (!$transactionId) {
            return [
                'success' => false,
                'transaction_id' => null,
                'verified' => false,
                'message' => 'شناسه تراکنش یافت نشد',
            ];
        }

        $transaction = Transaction::find($transactionId);

        if (!$transaction) {
            return [
                'success' => false,
                'transaction_id' => null,
                'verified' => false,
                'message' => 'تراکنش یافت نشد',
            ];
        }

        return [
            'success' => true,
            'transaction_id' => $transaction->id,
            'verified' => $transaction->isVerified(),
            'message' => 'در انتظار تایید پرداخت',
        ];
    }

    /**
     * Get gateway display name
     */
    public function getDisplayName(): string
    {
        return $this->gateway->display_name ?? 'پرداخت کارت به کارت';
    }

    /**
     * Check if gateway is available
     */
    public function isAvailable(): bool
    {
        return $this->gateway->is_active && !empty($this->cardNumber) && !empty($this->cardHolder);
    }

    /**
     * Get card number (formatted)
     */
    public function getCardNumber(): string
    {
        return $this->cardNumber;
    }

    /**
     * Get card holder name
     */
    public function getCardHolder(): string
    {
        return $this->cardHolder;
    }
}

