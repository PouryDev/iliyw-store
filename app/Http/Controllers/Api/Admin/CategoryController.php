<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryRepositoryInterface $categoryRepository
    ) {}

    /**
     * Get all categories
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 50);

        $categories = $this->categoryRepository
            ->query()
            ->withCount('products')
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => CategoryResource::collection($categories->items()),
            'pagination' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'total' => $categories->total(),
            ]
        ]);
    }

    /**
     * Store new category
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            $category = $this->categoryRepository->create($request->only([
                'name', 'slug', 'description', 'is_active'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'دسته‌بندی با موفقیت ایجاد شد',
                'data' => new CategoryResource($category)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ایجاد دسته‌بندی: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show category details
     */
    public function show(int $id): JsonResponse
    {
        $category = $this->categoryRepository->find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'دسته‌بندی یافت نشد'
            ], 404);
        }

        $category->load('products');

        return response()->json([
            'success' => true,
            'data' => new CategoryResource($category)
        ]);
    }

    /**
     * Update category
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:categories,slug,' . $id,
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        try {
            $updated = $this->categoryRepository->update($id, $request->only([
                'name', 'slug', 'description', 'is_active'
            ]));

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'دسته‌بندی یافت نشد'
                ], 404);
            }

            $category = $this->categoryRepository->find($id);

            return response()->json([
                'success' => true,
                'message' => 'دسته‌بندی با موفقیت به‌روزرسانی شد',
                'data' => new CategoryResource($category)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در به‌روزرسانی دسته‌بندی: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete category
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->categoryRepository->delete($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'دسته‌بندی یافت نشد'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'دسته‌بندی با موفقیت حذف شد'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف دسته‌بندی: ' . $e->getMessage()
            ], 500);
        }
    }
}

