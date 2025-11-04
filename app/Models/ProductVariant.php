<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

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
            $proposed = $variant->sku ?: $variant->generateSku();
            $variant->sku = self::sanitizeSku($proposed, $variant);
        });

        static::updating(function ($variant) {
            $proposed = $variant->sku ?: $variant->generateSku();
            $variant->sku = self::sanitizeSku($proposed, $variant);
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
        $productSlug = $this->product?->slug ?: Str::slug($this->product?->title ?? 'product', '-');

        $colorPart = $this->color ? ($this->getColorCode($this->color->name) ?: $this->color->id) : null;
        $sizePart = $this->size ? ($this->size->name ?: $this->size->id) : null;

        // Slugify parts to keep ASCII only
        $parts = array_filter([
            Str::slug(Str::ascii($productSlug), '-'),
            $colorPart ? Str::upper(Str::slug(Str::ascii($colorPart), '-')) : null,
            $sizePart ? Str::upper(Str::slug(Str::ascii($sizePart), '-')) : null,
        ]);

        $baseSku = implode('-', $parts);
        $uniqueSku = $baseSku ?: ('PROD-' . ($this->product?->id ?? 'X'));
        $counter = 1;
        
        while (self::where('sku', $uniqueSku)->where('id', '!=', $this->id ?? 0)->exists()) {
            $uniqueSku = $baseSku . '-' . $counter++;
        }
        
        return self::sanitizeSku($uniqueSku, $this);
    }

    protected static function sanitizeSku($value, $variant): string
    {
        // fallback if empty
        if (!$value) {
            $value = 'PROD-' . ($variant->product?->id ?? 'X');
        }
        $ascii = Str::ascii($value);
        $slug = Str::slug($ascii, '-');
        $upper = Str::upper($slug);
        return Str::limit($upper, 64, '');
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