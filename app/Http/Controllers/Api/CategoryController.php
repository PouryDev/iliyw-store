<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryRepositoryInterface $categoryRepository
    ) {}

    /**
     * Get all active categories (public)
     */
    public function index(): JsonResponse
    {
        $categories = $this->categoryRepository->getActive(['products']);

        return response()->json([
            'success' => true,
            'data' => CategoryResource::collection($categories)
        ]);
    }

    /**
     * Get category by slug
     */
    public function show(string $slug): JsonResponse
    {
        $category = $this->categoryRepository->findBySlug($slug, ['products.images']);

        if (!$category || !$category->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'دسته‌بندی یافت نشد'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new CategoryResource($category)
        ]);
    }
}
