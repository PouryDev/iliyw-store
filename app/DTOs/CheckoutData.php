<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class CheckoutData extends BaseDTO
{
    public function __construct(
        public readonly ?int $userId = null,
        public readonly string $customerName = '',
        public readonly string $customerPhone = '',
        public readonly string $customerAddress = '',
        public readonly int $deliveryMethodId = 0,
        public readonly ?int $deliveryAddressId = null,
        public readonly ?string $discountCode = null,
        public readonly ?int $paymentGatewayId = null,
        public readonly ?string $receiptPath = null,
        public readonly array $cart = [],
    ) {}

    /**
     * Create from request
     *
     * @param Request $request
     * @param array $cart
     * @return self
     */
    public static function fromRequest(Request $request, array $cart = []): self
    {
        return new self(
            userId: $request->user()?->id,
            customerName: $request->input('customer_name'),
            customerPhone: $request->input('customer_phone'),
            customerAddress: $request->input('customer_address'),
            deliveryMethodId: $request->input('delivery_method_id'),
            deliveryAddressId: $request->input('delivery_address_id'),
            discountCode: $request->input('discount_code'),
            paymentGatewayId: $request->input('payment_gateway_id'),
            cart: $cart,
        );
    }
}

