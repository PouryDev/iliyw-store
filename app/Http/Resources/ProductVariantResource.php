<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
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
            'product_id' => $this->product_id,
            'color_id' => $this->color_id,
            'size_id' => $this->size_id,
            'color' => $this->whenLoaded('color'),
            'size' => $this->whenLoaded('size'),
            'sku' => $this->sku,
            'price' => $this->price,
            'stock' => $this->stock,
            'is_active' => (bool) $this->is_active,
            'display_name' => $this->display_name,
        ];
    }
}

