<?php

namespace App\Repositories\Contracts;

use App\Models\DiscountCode;
use Illuminate\Database\Eloquent\Collection;

interface DiscountCodeRepositoryInterface extends RepositoryInterface
{
    /**
     * Find discount code by code
     *
     * @param string $code
     * @return DiscountCode|null
     */
    public function findByCode(string $code): ?DiscountCode;

    /**
     * Get active discount codes
     *
     * @return Collection
     */
    public function getActive(): Collection;

    /**
     * Check if code is valid for user
     *
     * @param string $code
     * @param int|null $userId
     * @return bool
     */
    public function isValidForUser(string $code, ?int $userId): bool;

    /**
     * Increment usage count
     *
     * @param int $codeId
     * @return bool
     */
    public function incrementUsage(int $codeId): bool;

    /**
     * Toggle discount code active status
     *
     * @param int $codeId
     * @return bool
     */
    public function toggleActive(int $codeId): bool;
}

