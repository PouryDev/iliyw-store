<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'description',
        'price',
        'stock',
        'has_variants',
        'has_colors',
        'has_sizes',
        'is_active',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function activeVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->where('is_active', true);
    }

    public function getTotalStockAttribute(): int
    {
        if ($this->has_variants) {
            return $this->activeVariants()->sum('stock');
        }
        
        return $this->stock;
    }

    public function getAvailableColorsAttribute()
    {
        if (!$this->has_colors) {
            return collect();
        }
        
        return Color::whereIn('id', $this->activeVariants()->pluck('color_id')->unique())
            ->where('is_active', true)
            ->get();
    }

    public function getAvailableSizesAttribute()
    {
        if (!$this->has_sizes) {
            return collect();
        }
        
        return Size::whereIn('id', $this->activeVariants()->pluck('size_id')->unique())
            ->where('is_active', true)
            ->get();
    }

    public function campaigns()
    {
        return $this->morphToMany(Campaign::class, 'targetable', 'campaign_targets');
    }

    public function getActiveCampaignsAttribute()
    {
        return $this->campaigns()->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->orderBy('priority', 'desc')
            ->get();
    }

    public function getBestCampaignAttribute()
    {
        return $this->active_campaigns->first();
    }

    public function getCampaignPriceAttribute()
    {
        $campaign = $this->best_campaign;
        if (!$campaign) {
            return $this->price;
        }
        
        return $campaign->getDiscountPrice($this->price);
    }

    public function getCampaignDiscountAmountAttribute()
    {
        $campaign = $this->best_campaign;
        if (!$campaign) {
            return 0;
        }
        
        return $campaign->calculateDiscount($this->price);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}


