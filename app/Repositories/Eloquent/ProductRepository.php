<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    /**
     * Get active products
     */
    public function getActive(array $relations = []): Collection
    {
        return $this->model->with($relations)
            ->where('is_active', true)
            ->latest()
            ->get();
    }

    /**
     * Get products by category
     */
    public function getByCategory(int $categoryId, array $relations = []): Collection
    {
        return $this->model->with($relations)
            ->where('category_id', $categoryId)
            ->where('is_active', true)
            ->latest()
            ->get();
    }

    /**
     * Search products (optimized with fulltext search for MySQL)
     */
    public function search(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $queryBuilder = $this->model->query()
            ->with(['images', 'campaigns' => function ($q) {
                $q->where('is_active', true)
                    ->where('starts_at', '<=', now())
                    ->where('ends_at', '>=', now())
                    ->orderBy('priority', 'desc');
            }])
            ->where('is_active', true);

        // Use FULLTEXT search for MySQL/MariaDB (faster for large datasets)
        if (DB::getDriverName() === 'mysql' && strlen($query) >= 3) {
            $queryBuilder->whereRaw(
                "MATCH(title, description) AGAINST(? IN BOOLEAN MODE)",
                [$query . '*']
            );
        } else {
            // Fallback to LIKE for shorter queries or other databases
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            });
        }

        return $queryBuilder->latest()->paginate($perPage);
    }

    /**
     * Get products with filters and pagination
     */
    public function getFiltered(array $filters = [], int $perPage = 15, ?string $sort = null): LengthAwarePaginator
    {
        $query = $this->model->query()
            ->with(['images', 'campaigns' => function ($q) {
                $q->where('is_active', true)
                    ->where('starts_at', '<=', now())
                    ->where('ends_at', '>=', now())
                    ->orderBy('priority', 'desc');
            }])
            ->where('is_active', true);

        // Apply filters
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['search'])) {
            $searchQuery = $filters['search'];
            
            // Use FULLTEXT search for MySQL/MariaDB (faster for large datasets)
            if (DB::getDriverName() === 'mysql' && strlen($searchQuery) >= 3) {
                $query->whereRaw(
                    "MATCH(title, description) AGAINST(? IN BOOLEAN MODE)",
                    [$searchQuery . '*']
                );
            } else {
                // Fallback to LIKE for shorter queries or other databases
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('title', 'like', "%{$searchQuery}%")
                        ->orWhere('description', 'like', "%{$searchQuery}%");
                });
            }
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', (int) $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', (int) $filters['max_price']);
        }

        if (!empty($filters['colors'])) {
            $colors = is_array($filters['colors']) ? $filters['colors'] : explode(',', $filters['colors']);
            $query->whereExists(function ($sub) use ($colors) {
                $sub->selectRaw('1')
                    ->from('product_variants as pv')
                    ->whereColumn('pv.product_id', 'products.id')
                    ->where('pv.is_active', true)
                    ->whereIn('pv.color_id', $colors);
            });
        }

        if (!empty($filters['sizes'])) {
            $sizes = is_array($filters['sizes']) ? $filters['sizes'] : explode(',', $filters['sizes']);
            $query->whereExists(function ($sub) use ($sizes) {
                $sub->selectRaw('1')
                    ->from('product_variants as pv')
                    ->whereColumn('pv.product_id', 'products.id')
                    ->where('pv.is_active', true)
                    ->whereIn('pv.size_id', $sizes);
            });
        }

        // Apply sorting
        $this->applySorting($query, $sort);

        return $query->paginate($perPage);
    }

    /**
     * Find product by slug
     */
    public function findBySlug(string $slug, array $relations = []): ?Product
    {
        return $this->model->with($relations)->where('slug', $slug)->first();
    }

    /**
     * Get best-selling products
     */
    public function getBestSelling(int $limit = 10): Collection
    {
        $soldSub = DB::table('order_items as oi')
            ->selectRaw('oi.product_id, SUM(oi.quantity) as sold_qty')
            ->groupBy('oi.product_id');

        return $this->model->query()
            ->with(['images'])
            ->where('is_active', true)
            ->leftJoinSub($soldSub, 'sales', function ($join) {
                $join->on('sales.product_id', '=', 'products.id');
            })
            ->select('products.*')
            ->orderByRaw('COALESCE(sales.sold_qty, 0) desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Apply sorting to query
     */
    protected function applySorting($query, ?string $sort): void
    {
        if ($sort === 'random') {
            $query->inRandomOrder();
        } elseif ($sort === 'cheapest') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'priciest') {
            $query->orderBy('price', 'desc');
        } elseif ($sort === 'best_seller') {
            $soldSub = DB::table('order_items as oi')
                ->selectRaw('oi.product_id, SUM(oi.quantity) as sold_qty')
                ->groupBy('oi.product_id');
            
            $query->leftJoinSub($soldSub, 'sales', function ($join) {
                $join->on('sales.product_id', '=', 'products.id');
            })
            ->select('products.*')
            ->orderByRaw('COALESCE(sales.sold_qty, 0) desc')
            ->latest('products.id');
        } else {
            $query->latest();
        }
    }
}

