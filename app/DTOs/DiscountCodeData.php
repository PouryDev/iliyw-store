<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class DiscountCodeData extends BaseDTO
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $code = null,
        public readonly ?string $type = null,
        public readonly ?int $value = null,
        public readonly ?int $minOrderAmount = null,
        public readonly ?string $startsAt = null,
        public readonly ?string $expiresAt = null,
        public readonly ?int $usageLimit = null,
        public readonly ?string $description = null,
        public readonly ?bool $isActive = null,
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
            code: $request->input('code'),
            type: $request->input('type'),
            value: $request->input('value'),
            minOrderAmount: $request->input('minimum_amount') ?? $request->input('min_order_amount'),
            startsAt: $request->input('starts_at'),
            expiresAt: $request->input('expires_at'),
            usageLimit: $request->input('usage_limit'),
            description: $request->input('description'),
            isActive: $request->boolean('is_active', true),
        );
    }
}

