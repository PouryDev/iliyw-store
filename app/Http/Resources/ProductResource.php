<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', fn() => new CategoryResource($this->category)),
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'has_variants' => (bool) $this->has_variants,
            'has_colors' => (bool) $this->has_colors,
            'has_sizes' => (bool) $this->has_sizes,
            'is_active' => (bool) $this->is_active,
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'variants' => $this->when(
                $this->relationLoaded('variants') || $this->relationLoaded('activeVariants'),
                function () {
                    if ($this->relationLoaded('variants')) {
                        return ProductVariantResource::collection($this->variants);
                    }
                    return ProductVariantResource::collection($this->activeVariants);
                }
            ),
            'available_colors' => $this->when($this->has_colors, fn() => $this->available_colors),
            'available_sizes' => $this->when($this->has_sizes, fn() => $this->available_sizes),
            'total_stock' => $this->when(isset($this->has_variants), fn() => $this->total_stock),
            'campaigns' => CampaignResource::collection($this->whenLoaded('campaigns')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

