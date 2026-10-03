<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
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
            'name' => $this->name,
            'title' => $this->name, // frontend alias
            'description' => $this->description,
            'type' => $this->type,
            'discount_type' => $this->type, // frontend alias
            'discount_value' => $this->discount_value,
            'max_discount_amount' => $this->max_discount_amount,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'expires_at' => $this->ends_at?->toISOString(), // frontend alias
            'priority' => $this->priority,
            'is_active' => (bool) $this->is_active,
            'badge_text' => $this->badge_text,
            'banner_image' => $this->banner_image,
            'products' => $this->whenLoaded('products', function () {
                return $this->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'title' => $product->title,
                        'price' => $product->price,
                        'images' => $product->relationLoaded('images')
                            ? ProductImageResource::collection($product->images)
                            : [],
                    ];
                });
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
