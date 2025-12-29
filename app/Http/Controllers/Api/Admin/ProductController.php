<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    /**
     * Get all products (admin)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 20);
        $search = $request->input('search');

        $products = $this->productRepository->getAllPaginated(
            $perPage,
            ['images', 'category', 'activeVariants'],
            $search
        );

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products->items()),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ]
        ]);
    }

    /**
     * Store new product
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $productData = $request->validated();

            DB::beginTransaction();

            $product = $this->productRepository->create($productData);

            // Handle images if provided
            if ($request->has('images')) {
                // TODO: Implement image handling
            }

            // Handle variants if provided
            if ($request->has('variants')) {
                // TODO: Implement variant handling
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'محصول با موفقیت ایجاد شد',
                'data' => new ProductResource($product->fresh(['images', 'activeVariants']))
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'خطا در ایجاد محصول: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show product details
     */
    public function show(int $id): JsonResponse
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'محصول یافت نشد'
            ], 404);
        }

        $product->load(['images', 'activeVariants.color', 'activeVariants.size', 'category', 'campaigns']);

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product)
        ]);
    }

    /**
     * Update product
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $updated = $this->productRepository->update($id, $request->validated());

            if (!$updated) {
                DB::rollBack();
                
                return response()->json([
                    'success' => false,
                    'message' => 'محصول یافت نشد'
                ], 404);
            }

            $product = $this->productRepository->find($id);

            // Handle images if provided
            if ($request->has('images')) {
                // TODO: Implement image update
            }

            // Handle variants if provided
            if ($request->has('variants')) {
                // TODO: Implement variant update
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'محصول با موفقیت به‌روزرسانی شد',
                'data' => new ProductResource($product->fresh(['images', 'activeVariants']))
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'خطا در به‌روزرسانی محصول: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete product
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->productRepository->delete($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'محصول یافت نشد'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'محصول با موفقیت حذف شد'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف محصول: ' . $e->getMessage()
            ], 500);
        }
    }
}

