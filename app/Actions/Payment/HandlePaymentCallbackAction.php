<?php

namespace App\Actions\Payment;

use App\Actions\BaseAction;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use App\Services\Payment\PaymentGatewayFactory;
use App\Exceptions\PaymentException;
use Illuminate\Support\Facades\Log;

class HandlePaymentCallbackAction extends BaseAction
{
    public function __construct(
        protected VerifyPaymentAction $verifyPaymentAction
    ) {}

    /**
     * Handle payment callback from gateway
     *
     * @param string $gatewayType
     * @param array $callbackData
     * @return array
     * @throws PaymentException
     */
    public function execute(...$params): array
    {
        [$gatewayType, $callbackData] = $params;

        $gateway = PaymentGateway::where('type', $gatewayType)->first();

        if (!$gateway) {
            Log::error('Payment gateway not found', [
                'gateway_type' => $gatewayType,
            ]);

            throw PaymentException::invalidGateway('درگاه پرداخت یافت نشد');
        }

        $gatewayInstance = PaymentGatewayFactory::create($gateway);
        $result = $gatewayInstance->callback($callbackData);

        if (!$result['success'] || !$result['transaction_id']) {
            Log::warning('Payment callback failed', [
                'gateway_type' => $gatewayType,
                'result' => $result,
            ]);

            return [
                'success' => false,
                'message' => $result['message'] ?? 'خطا در پردازش پرداخت',
            ];
        }

        $transaction = Transaction::find($result['transaction_id']);

        if (!$transaction) {
            Log::error('Transaction not found', [
                'transaction_id' => $result['transaction_id'],
            ]);

            throw PaymentException::verificationFailed('تراکنش یافت نشد');
        }

        // Update transaction with callback data
        $transaction->update([
            'callback_data' => $callbackData,
        ]);

        // Verify payment
        return $this->verifyPaymentAction->execute($transaction, $callbackData);
    }
}

