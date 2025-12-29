<?php

namespace App\Repositories\Contracts;

use App\Models\Invoice;

interface InvoiceRepositoryInterface extends RepositoryInterface
{
    /**
     * Find invoice by invoice number
     *
     * @param string $invoiceNumber
     * @return Invoice|null
     */
    public function findByInvoiceNumber(string $invoiceNumber): ?Invoice;

    /**
     * Find invoice by order
     *
     * @param int $orderId
     * @return Invoice|null
     */
    public function findByOrder(int $orderId): ?Invoice;

    /**
     * Update invoice status
     *
     * @param int $invoiceId
     * @param string $status
     * @return bool
     */
    public function updateStatus(int $invoiceId, string $status): bool;

    /**
     * Mark invoice as paid
     *
     * @param int $invoiceId
     * @return bool
     */
    public function markAsPaid(int $invoiceId): bool;
}

