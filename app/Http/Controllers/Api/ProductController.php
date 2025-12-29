<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    /**
     * Get products list with filters
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'search' => $request->input('q'),
            'category_id' => $request->input('category_id'),
            'min_price' => $request->input('min_price'),
            'max_price' => $request->input('max_price'),
            'colors' => $request->input('colors'),
            'sizes' => $request->input('sizes'),
        ];

        $perPage = (int) $request->input('per_page', 12);
        $sort = $request->input('sort', 'newest');

        $products = $this->productRepository->getFiltered($filters, $perPage, $sort);

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products->items()),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'has_more_pages' => $products->hasMorePages(),
            ]
        ]);
    }

    /**
     * Get product details
     */
    public function show(string $productSlug): JsonResponse
    {
        $product = $this->productRepository->findBySlug($productSlug, [
            'images',
            'activeVariants.color',
            'activeVariants.size',
            'campaigns'
        ]);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'محصول یافت نشد'
            ], 404);
        }

        // Add appends for computed attributes
        $product->setAppends(['available_colors', 'available_sizes', 'total_stock']);

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product)
        ]);
    }

    /**
     * Search products
     */
    public function search(Request $request): JsonResponse
    {
        $searchQuery = $request->input('q', '');
        $perPage = 12;

        $products = $this->productRepository->search($searchQuery, [], $perPage);

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products->items()),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'has_more_pages' => $products->hasMorePages(),
            ]
        ]);
    }
}
