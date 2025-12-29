<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'product_id' => $this->product_id,
            'product' => $this->whenLoaded('product', fn() => new ProductResource($this->product)),
            'product_variant_id' => $this->product_variant_id,
            'color_id' => $this->color_id,
            'size_id' => $this->size_id,
            'variant_display_name' => $this->variant_display_name,
            'campaign_id' => $this->campaign_id,
            'original_price' => $this->original_price,
            'campaign_discount_amount' => $this->campaign_discount_amount,
            'unit_price' => $this->unit_price,
            'quantity' => $this->quantity,
            'line_total' => $this->line_total,
        ];
    }
}

