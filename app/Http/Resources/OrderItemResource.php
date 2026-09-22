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
        $product = $this->relationLoaded('product') ? $this->product : null;
        $color = $this->relationLoaded('color') ? $this->color : null;
        $size = $this->relationLoaded('size') ? $this->size : null;
        $image = ($product && $product->relationLoaded('images'))
            ? $product->images->first()
            : null;

        $colorName = $color?->name;
        $sizeName = $size?->name;
        $variantDisplayName = $this->variant_display_name
            ?: (implode(' - ', array_filter([$colorName, $sizeName])) ?: null);

        $unitPrice = (int) $this->unit_price;
        $quantity = (int) $this->quantity;
        $lineTotal = $this->line_total !== null
            ? (int) $this->line_total
            : $unitPrice * $quantity;
        $originalPrice = $this->original_price !== null
            ? (int) $this->original_price
            : $unitPrice;

        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'product' => $this->whenLoaded('product', fn () => new ProductResource($this->product)),
            'product_title' => $product?->title,
            'product_image' => $image?->url,
            'product_variant_id' => $this->product_variant_id,
            'color_id' => $this->color_id,
            'size_id' => $this->size_id,
            'color' => $this->when($color !== null, fn () => [
                'id' => $color->id,
                'name' => $color->name,
                'hex_code' => $color->hex_code,
            ]),
            'size' => $this->when($size !== null, fn () => [
                'id' => $size->id,
                'name' => $size->name,
            ]),
            'color_name' => $colorName,
            'size_name' => $sizeName,
            'variant_display_name' => $variantDisplayName,
            'campaign_id' => $this->campaign_id,
            'original_price' => $originalPrice,
            'campaign_discount_amount' => (int) ($this->campaign_discount_amount ?? 0),
            'unit_price' => $unitPrice,
            'price' => $unitPrice,
            'quantity' => $quantity,
            'line_total' => $lineTotal,
        ];
    }
}
