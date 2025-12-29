<?php

namespace App\Repositories\Eloquent;

use App\Models\DiscountCode;
use App\Repositories\Contracts\DiscountCodeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class DiscountCodeRepository extends BaseRepository implements DiscountCodeRepositoryInterface
{
    public function __construct(DiscountCode $model)
    {
        $this->model = $model;
    }

    /**
     * Find discount code by code
     */
    public function findByCode(string $code): ?DiscountCode
    {
        return $this->model->where('code', $code)->first();
    }

    /**
     * Get active discount codes
     */
    public function getActive(): Collection
    {
        return $this->model->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->get();
    }

    /**
     * Check if code is valid for user
     */
    public function isValidForUser(string $code, ?int $userId): bool
    {
        $discountCode = $this->findByCode($code);

        if (!$discountCode || !$discountCode->is_active) {
            return false;
        }

        // Check date validity
        if ($discountCode->starts_at && $discountCode->starts_at > now()) {
            return false;
        }

        if ($discountCode->expires_at && $discountCode->expires_at < now()) {
            return false;
        }

        // Check usage limit
        if ($discountCode->usage_limit) {
            $usageCount = $discountCode->usages()->count();
            if ($usageCount >= $discountCode->usage_limit) {
                return false;
            }
        }

        // Check if user has already used this code
        if ($userId) {
            $hasUsed = $discountCode->usages()
                ->where('user_id', $userId)
                ->exists();
            
            if ($hasUsed) {
                return false;
            }
        }

        return true;
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(int $codeId): bool
    {
        // This is handled by DiscountCodeUsage model
        // but we can add a counter cache if needed
        return true;
    }

    /**
     * Toggle discount code active status
     */
    public function toggleActive(int $codeId): bool
    {
        $code = $this->findOrFail($codeId);
        return $code->update(['is_active' => !$code->is_active]);
    }
}

