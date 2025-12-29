<?php

namespace App\Actions\Payment;

use App\Actions\BaseAction;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\PaymentGateway;
use App\Services\Payment\PaymentGatewayFactory;
use App\Exceptions\PaymentException;
use App\Enums\TransactionStatus;

class InitiatePaymentAction extends BaseAction
{
    /**
     * Initiate payment for invoice
     *
     * @param Invoice $invoice
     * @param int $gatewayId
     * @param array $additionalData
     * @return array [
     *   'success' => bool,
     *   'transaction_id' => int,
     *   'redirect_url' => string,
     *   'form_data' => array,
     *   'message' => string
     * ]
     * @throws PaymentException
     */
    public function execute(...$params): array
    {
        [$invoice, $gatewayId, $additionalData] = $params + [null, null, []];

        $gateway = PaymentGateway::find($gatewayId);

        if (!$gateway) {
            throw PaymentException::invalidGateway();
        }

        if (!$gateway->is_active) {
            throw PaymentException::invalidGateway('این درگاه پرداخت فعال نیست');
        }

        $gatewayInstance = PaymentGatewayFactory::create($gateway);

        if (!$gatewayInstance->isAvailable()) {
            throw PaymentException::gatewayUnavailable();
        }

        // Create transaction record
        $transaction = Transaction::create([
            'invoice_id' => $invoice->id,
            'gateway_id' => $gateway->id,
            'method' => $gateway->type,
            'amount' => $invoice->amount,
            'status' => TransactionStatus::PENDING->value,
        ]);

        // Update invoice with gateway
        $invoice->update([
            'payment_gateway_id' => $gateway->id,
        ]);

        // Initiate payment with gateway
        $result = $gatewayInstance->initiate($invoice, array_merge($additionalData, [
            'transaction_id' => $transaction->id,
        ]));

        if (!$result['success']) {
            $transaction->update([
                'status' => TransactionStatus::REJECTED->value,
            ]);

            throw PaymentException::initiationFailed($result['message'] ?? 'خطا در شروع پرداخت');
        }

        // Update transaction with gateway transaction ID
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
    }
}

