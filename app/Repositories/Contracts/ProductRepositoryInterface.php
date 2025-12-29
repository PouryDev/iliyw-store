<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface extends RepositoryInterface
{
    /**
     * Get active products
     *
     * @param array $relations
     * @return Collection
     */
    public function getActive(array $relations = []): Collection;

    /**
     * Get products by category
     *
     * @param int $categoryId
     * @param array $relations
     * @return Collection
     */
    public function getByCategory(int $categoryId, array $relations = []): Collection;

    /**
     * Search products
     *
     * @param string $query
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get products with filters and pagination
     *
     * @param array $filters
     * @param int $perPage
     * @param string|null $sort
     * @return LengthAwarePaginator
     */
    public function getFiltered(array $filters = [], int $perPage = 15, ?string $sort = null): LengthAwarePaginator;

    /**
     * Find product by slug
     *
     * @param string $slug
     * @param array $relations
     * @return Product|null
     */
    public function findBySlug(string $slug, array $relations = []): ?Product;

    /**
     * Get best-selling products
     *
     * @param int $limit
     * @return Collection
     */
    public function getBestSelling(int $limit = 10): Collection;
}

