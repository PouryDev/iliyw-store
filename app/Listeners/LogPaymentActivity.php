<?php

namespace App\Listeners;

use App\Events\PaymentVerified;
use App\Events\PaymentFailed;
use Illuminate\Support\Facades\Log;

class LogPaymentActivity
{
    /**
     * Handle payment verified event
     */
    public function handleVerified(PaymentVerified $event): void
    {
        Log::info('[PaymentVerified] Payment successfully verified', [
            'transaction_id' => $event->transaction->id,
            'invoice_id' => $event->invoice->id,
            'amount' => $event->invoice->amount,
        ]);
    }

    /**
     * Handle payment failed event
     */
    public function handleFailed(PaymentFailed $event): void
    {
        Log::warning('[PaymentFailed] Payment verification failed', [
            'transaction_id' => $event->transaction->id,
            'reason' => $event->reason,
        ]);
    }
}

