<?php

namespace App\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface extends RepositoryInterface
{
    /**
     * Get active categories
     *
     * @return Collection
     */
    public function getActive(): Collection;

    /**
     * Get categories with product count
     *
     * @return Collection
     */
    public function getWithProductCount(): Collection;

    /**
     * Find category by slug
     *
     * @param string $slug
     * @return Category|null
     */
    public function findBySlug(string $slug): ?Category;

    /**
     * Toggle category active status
     *
     * @param int $categoryId
     * @return bool
     */
    public function toggleActive(int $categoryId): bool;
}

