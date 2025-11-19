<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'color_id',
        'size_id',
        'sku',
        'stock',
        'price',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($variant) {
            if (empty($variant->sku)) {
                $variant->sku = $variant->generateSku();
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    public function campaigns()
    {
        return $this->product->campaigns();
    }

    public function getActiveCampaignsAttribute()
    {
        return $this->product->active_campaigns;
    }

    public function getBestCampaignAttribute()
    {
        return $this->product->best_campaign;
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

    public function getDisplayNameAttribute(): string
    {
        $parts = [];
        
        if ($this->color) {
            $parts[] = $this->color->name;
        }
        
        if ($this->size) {
            $parts[] = $this->size->name;
        }
        
        return implode(' - ', $parts) ?: 'بدون تنوع';
    }

    public function getPriceAttribute($value): int
    {
        // اگر قیمت مخصوص variant تعین نشده، از قیمت محصول استفاده کن
        return $value ?? $this->product->price;
    }

    public function getSkuAttribute($value): string
    {
        return $value ?? '';
    }

    public function generateSku(): string
    {
        $productSku = $this->product->slug ?? 'PROD';
        
        $parts = [$productSku];
        
        if ($this->color) {
            // Convert Persian color names to ASCII
            $colorCode = $this->getColorCode($this->color->name);
            $parts[] = strtoupper($colorCode);
        }
        
        if ($this->size) {
            // Size names are usually already in English (XS, S, M, L, XL, XXL)
            $parts[] = strtoupper($this->size->name);
        }
        
        $baseSku = implode('-', array_filter($parts));
        $uniqueSku = $baseSku;
        $counter = 1;
        
        while (self::where('sku', $uniqueSku)->where('id', '!=', $this->id ?? 0)->exists()) {
            $uniqueSku = $baseSku . '-' . $counter++;
        }
        
        return $uniqueSku;
    }

    private function getColorCode(string $colorName): string
    {
        // Map Persian color names to English codes
        $colorMap = [
            'قرمز' => 'RED',
            'آبی' => 'BLU',
            'سبز' => 'GRN',
            'مشکی' => 'BLK',
            'سفید' => 'WHT',
            'خاکستری' => 'GRY',
            'صورتی' => 'PNK',
            'بنفش' => 'PRP',
            'نارنجی' => 'ORG',
            'زرد' => 'YLW',
            'قهوه‌ای' => 'BRN',
            'طلایی' => 'GLD',
            'سیلور' => 'SLV',
            'نقره‌ای' => 'SLV',
            'برنجی' => 'BRZ',
            'گلد' => 'GLD',
            'چری' => 'BRN',
        ];
        
        return $colorMap[$colorName] ?? strtoupper(substr($colorName, 0, 3));
    }
}