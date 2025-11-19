<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Carbon\Carbon;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'discount_value',
        'max_discount_amount',
        'starts_at',
        'ends_at',
        'is_active',
        'priority',
        'banner_image',
        'badge_text',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function targets(): HasMany
    {
        return $this->hasMany(CampaignTarget::class);
    }

    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'targetable', 'campaign_targets');
    }

    public function categories(): MorphToMany
    {
        return $this->morphedByMany(Category::class, 'targetable', 'campaign_targets');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(CampaignSale::class);
    }

    public function isActive(): bool
    {
        $now = Carbon::now();
        return $this->is_active && 
               $this->starts_at <= $now && 
               $this->ends_at >= $now;
    }

    public function isUpcoming(): bool
    {
        return $this->starts_at > Carbon::now();
    }

    public function isExpired(): bool
    {
        return $this->ends_at < Carbon::now();
    }

    public function getStatusAttribute(): string
    {
        if ($this->isExpired()) {
            return 'expired';
        }
        
        if ($this->isUpcoming()) {
            return 'upcoming';
        }
        
        if ($this->isActive()) {
            return 'active';
        }
        
        return 'inactive';
    }

    public function calculateDiscount(int $originalPrice): int
    {
        if ($this->type === 'percentage') {
            $discount = ($originalPrice * $this->discount_value) / 100;
            if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
                $discount = $this->max_discount_amount;
            }
            return min($discount, $originalPrice);
        } elseif ($this->type === 'fixed') {
            return min($this->discount_value, $originalPrice);
        }
        
        return 0;
    }

    public function getDiscountPrice(int $originalPrice): int
    {
        return $originalPrice - $this->calculateDiscount($originalPrice);
    }

    public function getAffectedProducts()
    {
        $products = collect();
        
        // محصولات مستقیم
        $products = $products->merge($this->products);
        
        // محصولات دسته‌بندی‌ها
        foreach ($this->categories as $category) {
            $products = $products->merge($category->products);
        }
        
        return $products->unique('id');
    }

    public function getTotalSalesAttribute(): int
    {
        return $this->sales()->sum('total_discount');
    }

    public function getTotalQuantitySoldAttribute(): int
    {
        return $this->sales()->sum('quantity');
    }

    public function getTotalRevenueAttribute(): int
    {
        return $this->sales()->sum(\DB::raw('final_price * quantity'));
    }
}