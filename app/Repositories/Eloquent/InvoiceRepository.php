<?php

namespace App\Repositories\Eloquent;

use App\Models\Invoice;
use App\Repositories\Contracts\InvoiceRepositoryInterface;

class InvoiceRepository extends BaseRepository implements InvoiceRepositoryInterface
{
    public function __construct(Invoice $model)
    {
        $this->model = $model;
    }

    /**
     * Find invoice by invoice number
     */
    public function findByInvoiceNumber(string $invoiceNumber): ?Invoice
    {
        return $this->model->where('invoice_number', $invoiceNumber)->first();
    }

    /**
     * Find invoice by order
     */
    public function findByOrder(int $orderId): ?Invoice
    {
        return $this->model->where('order_id', $orderId)->first();
    }

    /**
     * Update invoice status
     */
    public function updateStatus(int $invoiceId, string $status): bool
    {
        return $this->update($invoiceId, ['status' => $status]);
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(int $invoiceId): bool
    {
        return $this->update($invoiceId, [
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }
}

