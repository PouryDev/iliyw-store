<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', fn() => new UserResource($this->user)),
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'customer_address' => $this->customer_address,
            'delivery_method_id' => $this->delivery_method_id,
            'delivery_method' => $this->whenLoaded('deliveryMethod', fn() => new DeliveryMethodResource($this->deliveryMethod)),
            'delivery_address_id' => $this->delivery_address_id,
            'delivery_address' => $this->customer_address,
            'delivery_fee' => (int) ($this->delivery_fee ?? 0),
            'amount' => (int) $this->total_amount,
            'total_amount' => (int) $this->total_amount,
            'original_amount' => (int) ($this->original_amount ?? $this->total_amount),
            'campaign_discount_amount' => (int) ($this->campaign_discount_amount ?? 0),
            'discount_code' => $this->discount_code,
            'discount_amount' => (int) ($this->discount_amount ?? 0),
            'final_amount' => (int) ($this->final_amount ?? $this->total_amount),
            'status' => $this->status,
            'receipt_path' => $this->receipt_path,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'invoice' => $this->whenLoaded('invoice', fn() => new InvoiceResource($this->invoice)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

