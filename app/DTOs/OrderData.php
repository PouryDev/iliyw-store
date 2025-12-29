<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class OrderData extends BaseDTO
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?int $userId = null,
        public readonly ?string $customerName = null,
        public readonly ?string $customerPhone = null,
        public readonly ?string $customerAddress = null,
        public readonly ?int $deliveryMethodId = null,
        public readonly ?int $deliveryAddressId = null,
        public readonly ?int $deliveryFee = null,
        public readonly ?int $totalAmount = null,
        public readonly ?int $originalAmount = null,
        public readonly ?int $campaignDiscountAmount = null,
        public readonly ?string $discountCode = null,
        public readonly ?int $discountAmount = null,
        public readonly ?int $finalAmount = null,
        public readonly ?string $status = null,
        public readonly ?string $receiptPath = null,
        public readonly ?array $items = null,
    ) {}

    /**
     * Create from request
     *
     * @param Request $request
     * @return self
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            userId: $request->user()?->id,
            customerName: $request->input('customer_name'),
            customerPhone: $request->input('customer_phone'),
            customerAddress: $request->input('customer_address'),
            deliveryMethodId: $request->input('delivery_method_id'),
            deliveryAddressId: $request->input('delivery_address_id'),
            discountCode: $request->input('discount_code'),
        );
    }
}

