<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CampaignTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'targetable_id',
        'targetable_type',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function targetable(): MorphTo
    {
        return $this->morphTo();
    }
}