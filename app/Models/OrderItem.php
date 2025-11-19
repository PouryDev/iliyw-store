<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'color_id',
        'size_id',
        'variant_display_name',
        'campaign_id',
        'original_price',
        'campaign_discount_amount',
        'unit_price',
        'quantity',
        'line_total',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function campaignSales(): HasMany
    {
        return $this->hasMany(CampaignSale::class);
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->variant_display_name) {
            return $this->product->title . ' - ' . $this->variant_display_name;
        }
        
        return $this->product->title;
    }

    public function productVariants(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}


