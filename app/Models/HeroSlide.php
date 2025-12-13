<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class HeroSlide extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'image_path',
        'link_type',
        'linkable_type',
        'linkable_id',
        'custom_url',
        'button_text',
        'click_count',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'click_count' => 'integer',
        'sort_order' => 'integer',
    ];

    protected $appends = ['image_url', 'link_url'];

    /**
     * Polymorphic relationship to Product, Category, or Campaign
     */
    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the full URL for the image
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        // If already an absolute URL, return as-is
        if (str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }

        // Normalize leading slashes
        $path = $this->image_path;
        if (str_starts_with($path, '/')) {
            $path = substr($path, 1);
        }

        return asset('storage/' . $path);
    }

    /**
     * Get the final link URL based on link_type
     */
    public function getLinkUrlAttribute(): ?string
    {
        if ($this->link_type === 'custom') {
            return $this->custom_url;
        }

        if (!$this->linkable) {
            return null;
        }

        switch ($this->link_type) {
            case 'product':
                return '/product/' . $this->linkable->slug;
            case 'category':
                return '/category/' . $this->linkable->id;
            case 'campaign':
                return '/products?campaign=' . $this->linkable->id;
            default:
                return null;
        }
    }

    /**
     * Increment click count
     */
    public function incrementClick(): void
    {
        $this->increment('click_count');
    }

    /**
     * Scope for active slides
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered slides
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }
}
