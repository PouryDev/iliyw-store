<?php

namespace App\DTOs;

class PaymentData extends BaseDTO
{
    public function __construct(
        public readonly int $invoiceId,
        public readonly int $gatewayId,
        public readonly int $amount,
        public readonly ?int $transactionId = null,
        public readonly ?string $authority = null,
        public readonly ?string $trackId = null,
        public readonly ?array $callbackData = null,
    ) {}

    /**
     * Create from invoice and gateway
     *
     * @param int $invoiceId
     * @param int $gatewayId
     * @param int $amount
     * @return self
     */
    public static function fromInvoice(int $invoiceId, int $gatewayId, int $amount): self
    {
        return new self(
            invoiceId: $invoiceId,
            gatewayId: $gatewayId,
            amount: $amount,
        );
    }
}

