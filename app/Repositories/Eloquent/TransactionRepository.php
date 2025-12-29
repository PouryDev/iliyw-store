<?php

namespace App\Repositories\Eloquent;

use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TransactionRepository extends BaseRepository implements TransactionRepositoryInterface
{
    public function __construct(Transaction $model)
    {
        $this->model = $model;
    }

    /**
     * Get transactions by invoice
     */
    public function getByInvoice(int $invoiceId): Collection
    {
        return $this->model->where('invoice_id', $invoiceId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Find transaction by gateway transaction ID
     */
    public function findByGatewayTransactionId(string $gatewayTransactionId): ?Transaction
    {
        return $this->model->where('gateway_transaction_id', $gatewayTransactionId)->first();
    }

    /**
     * Update transaction status
     */
    public function updateStatus(int $transactionId, string $status): bool
    {
        return $this->update($transactionId, ['status' => $status]);
    }

    /**
     * Mark transaction as verified
     */
    public function markAsVerified(int $transactionId, array $data = []): bool
    {
        return $this->update($transactionId, array_merge([
            'status' => 'verified',
            'verified_at' => now(),
        ], $data));
    }

    /**
     * Mark transaction as rejected
     */
    public function markAsRejected(int $transactionId, array $data = []): bool
    {
        return $this->update($transactionId, array_merge([
            'status' => 'rejected',
        ], $data));
    }
}

