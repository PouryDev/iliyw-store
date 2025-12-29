<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class CampaignData extends BaseDTO
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?string $type = null,
        public readonly ?int $discountValue = null,
        public readonly ?string $startsAt = null,
        public readonly ?string $endsAt = null,
        public readonly ?int $priority = null,
        public readonly ?bool $isActive = null,
        public readonly ?array $productIds = null,
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
            name: $request->input('title') ?? $request->input('name'),
            description: $request->input('description'),
            type: $request->input('discount_type') ?? $request->input('type'),
            discountValue: $request->input('discount_value'),
            startsAt: $request->input('starts_at'),
            endsAt: $request->input('expires_at') ?? $request->input('ends_at'),
            priority: $request->input('priority', 0),
            isActive: $request->boolean('is_active', true),
            productIds: $request->input('product_ids', []),
        );
    }
}

