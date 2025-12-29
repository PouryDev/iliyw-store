<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Actions\Payment\InitiatePaymentAction;
use App\Actions\Payment\VerifyPaymentAction;
use App\Actions\Payment\HandlePaymentCallbackAction;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\PaymentGateway;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Exceptions\PaymentException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        protected InitiatePaymentAction $initiatePaymentAction,
        protected VerifyPaymentAction $verifyPaymentAction,
        protected HandlePaymentCallbackAction $handleCallbackAction,
        protected InvoiceRepositoryInterface $invoiceRepository,
        protected TransactionRepositoryInterface $transactionRepository
    ) {}

    /**
     * Get active payment gateways
     */
    public function gateways(): JsonResponse
    {
        $gateways = PaymentGateway::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $gateways,
        ]);
    }

    /**
     * Initiate payment
     */
    public function initiate(Request $request): JsonResponse
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'gateway_id' => 'required|exists:payment_gateways,id',
        ]);

        try {
            $invoice = $this->invoiceRepository->findOrFail($request->invoice_id);

            // Check authorization
            if ($request->user() && $invoice->order && $invoice->order->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'شما دسترسی به این فاکتور ندارید',
                ], 403);
            }

            $gateway = PaymentGateway::findOrFail($request->gateway_id);

            $result = $this->initiatePaymentAction->execute(
                $invoice,
                $request->gateway_id,
                [
                    'callback_url' => route('payment.callback', ['gateway' => $gateway->type]),
                ]
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'transaction_id' => $result['transaction_id'],
                    'redirect_url' => $result['redirect_url'],
                    'form_data' => $result['form_data'],
                ],
                'message' => $result['message'],
            ]);

        } catch (PaymentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Handle callback from payment gateway
     */
    public function callback(Request $request, string $gateway)
    {
        $callbackData = $request->all();

        try {
            $result = $this->handleCallbackAction->execute($gateway, $callbackData);

            if ($result['success'] && $result['verified']) {
                // Redirect to success page
                $invoice = Invoice::findOrFail($result['invoice_id']);
                return redirect('/thanks/' . urlencode($invoice->invoice_number))
                    ->with('success', 'پرداخت با موفقیت انجام شد');
            }

            // Redirect to error page
            $errorMessage = $result['message'] ?? 'پرداخت انجام نشد یا توسط کاربر لغو شد';
            return redirect('/payment/error?message=' . urlencode($errorMessage));

        } catch (PaymentException $e) {
            return redirect('/payment/error?message=' . urlencode($e->getMessage()));
        } catch (ModelNotFoundException $e) {
            Log::error('Payment callback model not found', [
                'gateway' => $gateway,
                'callback_data' => $callbackData,
                'error' => $e->getMessage(),
            ]);

            return redirect('/payment/error?message=' . urlencode('اطلاعات پرداخت یافت نشد'));
        } catch (\Exception $e) {
            Log::error('Payment callback unexpected error', [
                'gateway' => $gateway,
                'callback_data' => $callbackData,
                'error' => $e->getMessage(),
            ]);

            return redirect('/payment/error?message=' . urlencode('خطا در پردازش پرداخت'));
        }
    }

    /**
     * Verify payment (for card-to-card with receipt)
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'receipt' => 'required|image|max:4096',
        ]);

        try {
            $transaction = $this->transactionRepository->findOrFail($request->transaction_id);
            $invoice = $transaction->invoice;

            // Check authorization
            if ($request->user() && $invoice->order && $invoice->order->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'شما دسترسی به این تراکنش ندارید',
                ], 403);
            }

            // Upload receipt
            $receiptPath = $request->file('receipt')->store('receipts', 'public');

            // Update transaction with receipt
            $this->transactionRepository->update($transaction->id, [
                'receipt_path' => $receiptPath,
                'status' => 'pending', // Pending admin verification
            ]);

            return response()->json([
                'success' => true,
                'message' => 'فیش واریزی با موفقیت آپلود شد. پس از تایید، سفارش شما پردازش خواهد شد.',
                'transaction_id' => $transaction->id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در آپلود فیش: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment status
     */
    public function status(Request $request, int $transactionId): JsonResponse
    {
        try {
            $transaction = $this->transactionRepository->findOrFail($transactionId);

            // Check authorization
            if ($request->user() && $transaction->invoice->order && 
                $transaction->invoice->order->user_id !== $request->user()->id) {
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
                    'verified' => $transaction->status === 'verified',
                    'invoice' => [
                        'id' => $transaction->invoice->id,
                        'invoice_number' => $transaction->invoice->invoice_number,
                        'status' => $transaction->invoice->status,
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'تراکنش یافت نشد',
            ], 404);
        }
    }
}
