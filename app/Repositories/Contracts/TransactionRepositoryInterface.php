<?php

namespace App\Repositories\Contracts;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

interface TransactionRepositoryInterface extends RepositoryInterface
{
    /**
     * Get transactions by invoice
     *
     * @param int $invoiceId
     * @return Collection
     */
    public function getByInvoice(int $invoiceId): Collection;

    /**
     * Find transaction by gateway transaction ID
     *
     * @param string $gatewayTransactionId
     * @return Transaction|null
     */
    public function findByGatewayTransactionId(string $gatewayTransactionId): ?Transaction;

    /**
     * Update transaction status
     *
     * @param int $transactionId
     * @param string $status
     * @return bool
     */
    public function updateStatus(int $transactionId, string $status): bool;

    /**
     * Mark transaction as verified
     *
     * @param int $transactionId
     * @param array $data
     * @return bool
     */
    public function markAsVerified(int $transactionId, array $data = []): bool;

    /**
     * Mark transaction as rejected
     *
     * @param int $transactionId
     * @param array $data
     * @return bool
     */
    public function markAsRejected(int $transactionId, array $data = []): bool;
}

