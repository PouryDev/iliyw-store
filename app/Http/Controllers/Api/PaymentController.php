<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Get active payment gateways
     */
    public function gateways()
    {
        $gateways = $this->paymentService->getActiveGateways();

        return response()->json([
            'success' => true,
            'data' => $gateways,
        ]);
    }

    /**
     * Initiate payment
     */
    public function initiate(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'gateway_id' => 'required|exists:payment_gateways,id',
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);

        // Check if invoice belongs to authenticated user
        if ($request->user() && $invoice->order && $invoice->order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'شما دسترسی به این فاکتور ندارید',
            ], 403);
        }

        $result = $this->paymentService->initiatePayment(
            $invoice,
            $request->gateway_id,
            [
                'callback_url' => route('payment.callback', ['gateway' => 'zarinpal']),
            ]
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'data' => [
                    'transaction_id' => $result['transaction_id'],
                    'redirect_url' => $result['redirect_url'],
                    'form_data' => $result['form_data'],
                ],
                'message' => $result['message'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 400);
    }

    /**
     * Handle callback from gateway
     */
    public function callback(Request $request, string $gateway)
    {
        $callbackData = $request->all();

        $result = $this->paymentService->handleCallback($gateway, $callbackData);

        if ($result['success'] && $result['verified']) {
            // Redirect to success page
            $invoice = Invoice::findOrFail($result['invoice_id']);
            return redirect('/thanks/' . urlencode($invoice->invoice_number))
                ->with('success', 'پرداخت با موفقیت انجام شد');
        }

        // Redirect to payment error page with error message
        $errorMessage = $result['message'] ?? 'پرداخت انجام نشد یا توسط کاربر لغو شد';
        return redirect('/payment/error?message=' . urlencode($errorMessage));
    }

    /**
     * Verify payment manually (for card-to-card)
     */
    public function verify(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'receipt' => 'required|image|max:4096',
        ]);

        $transaction = Transaction::findOrFail($request->transaction_id);

        // Check if transaction belongs to authenticated user
        if ($request->user() && $transaction->invoice->order && $transaction->invoice->order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'شما دسترسی به این تراکنش ندارید',
            ], 403);
        }

        // Upload receipt
        $receiptPath = $request->file('receipt')->store('receipts', 'public');

        // Update transaction
        $transaction->update([
            'receipt_path' => $receiptPath,
            'status' => 'pending', // Will be verified by admin
        ]);

        return response()->json([
            'success' => true,
            'message' => 'فیش واریزی با موفقیت آپلود شد. پس از تایید، سفارش شما پردازش خواهد شد.',
            'transaction_id' => $transaction->id,
        ]);
    }

    /**
     * Get payment status
     */
    public function status(Request $request, Transaction $transaction)
    {
        // Check if transaction belongs to authenticated user
        if ($request->user() && $transaction->invoice->order && $transaction->invoice->order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'شما دسترسی به این تراکنش ندارید',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $transaction->id,
                'status' => $transaction->status,
                'verified' => $transaction->isVerified(),
                'invoice' => [
                    'id' => $transaction->invoice->id,
                    'invoice_number' => $transaction->invoice->invoice_number,
                    'status' => $transaction->invoice->status,
                ],
            ],
        ]);
    }
}
