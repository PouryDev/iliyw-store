<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'path',
        'sort_order',
    ];

    protected $appends = ['url'];

    public function getUrlAttribute(): string
    {
        // Support external URLs used by seeder or local storage paths
        if (str_starts_with($this->path, 'http')) {
            return $this->path;
        }
        return asset('storage/'.$this->path);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}


