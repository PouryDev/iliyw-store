<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'gateway_id',
        'gateway_transaction_id',
        'callback_data',
        'method',
        'amount',
        'reference',
        'status',
        'receipt_path',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'callback_data' => 'array',
        'verified_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'gateway_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Check if transaction is verified
     */
    public function isVerified(): bool
    {
        return $this->status === 'verified' && $this->verified_at !== null;
    }

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if transaction is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Mark transaction as verified
     */
    public function markAsVerified(?int $verifiedBy = null): void
    {
        $this->update([
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => $verifiedBy,
        ]);
    }

    /**
     * Mark transaction as rejected
     */
    public function markAsRejected(): void
    {
        $this->update([
            'status' => 'rejected',
        ]);
    }
}


