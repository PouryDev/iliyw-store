<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model)
    {
        $this->model = $model;
    }

    /**
     * Get active categories
     */
    public function getActive(): Collection
    {
        return $this->model->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get categories with product count
     */
    public function getWithProductCount(): Collection
    {
        return $this->model->withCount(['products' => function ($query) {
            $query->where('is_active', true);
        }])
        ->orderBy('name')
        ->get();
    }

    /**
     * Get categories paginated with products count.
     */
    public function getAllPaginated(
        int $perPage = 15,
        array $relations = [],
        ?string $search = null
    ): LengthAwarePaginator {
        $query = $this->model->query()->withCount('products');

        if (!empty($relations)) {
            $query->with($relations);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Find category by slug
     */
    public function findBySlug(string $slug): ?Category
    {
        return $this->model->where('slug', $slug)->first();
    }

    /**
     * Toggle category active status
     */
    public function toggleActive(int $categoryId): bool
    {
        $category = $this->findOrFail($categoryId);
        return $category->update(['is_active' => !$category->is_active]);
    }
}

