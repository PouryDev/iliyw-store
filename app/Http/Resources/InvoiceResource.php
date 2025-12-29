<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'payment_gateway_id' => $this->payment_gateway_id,
            'invoice_number' => $this->invoice_number,
            'amount' => $this->amount,
            'original_amount' => $this->original_amount,
            'campaign_discount_amount' => $this->campaign_discount_amount,
            'discount_code_amount' => $this->discount_code_amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'paid_at' => $this->paid_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

