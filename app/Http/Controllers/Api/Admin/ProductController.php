<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Color;
use App\Models\ProductImage;
use App\Models\Size;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

            // Generate slug if not provided
            if (empty($productData['slug'])) {
                $productData['slug'] = Str::slug($productData['title']) . '-' . Str::random(4);
            }

            DB::beginTransaction();

            $product = $this->productRepository->create($productData);

            // Handle images if provided
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    $path = $file->store('products', 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $path,
                        'sort_order' => $index,
                    ]);
                }
            }

            // Handle variants if provided
            if ($request->has('variants')) {
                foreach ($request->input('variants') as $variantData) {
                    $variantDataToSave = [];
                    
                    // Process color
                    if (!empty($variantData['color_id'])) {
                        $variantDataToSave['color_id'] = (int) $variantData['color_id'];
                    } elseif (!empty($variantData['color_name'])) {
                        $colorName = trim($variantData['color_name']);
                        $color = Color::firstOrCreate(
                            ['name' => $colorName],
                            [
                                'hex_code' => $variantData['color_hex_code'] ?? null,
                                'is_active' => true
                            ]
                        );
                        
                        // Update hex_code if provided and different
                        if (!empty($variantData['color_hex_code']) && $color->hex_code !== $variantData['color_hex_code']) {
                            $color->hex_code = $variantData['color_hex_code'];
                            $color->save();
                        }
                        
                        $variantDataToSave['color_id'] = $color->id;
                    }
                    
                    // Process size
                    if (!empty($variantData['size_id'])) {
                        $variantDataToSave['size_id'] = (int) $variantData['size_id'];
                    } elseif (!empty($variantData['size_name'])) {
                        $sizeName = trim($variantData['size_name']);
                        $size = Size::firstOrCreate(
                            ['name' => $sizeName],
                            ['is_active' => true]
                        );
                        $variantDataToSave['size_id'] = $size->id;
                    }
                    
                    // Add other fields
                    $variantDataToSave['price'] = isset($variantData['price']) && $variantData['price'] !== '' 
                        ? (int) $variantData['price'] 
                        : null;
                    $variantDataToSave['stock'] = isset($variantData['stock']) && $variantData['stock'] !== '' 
                        ? (int) $variantData['stock'] 
                        : 0;
                    $variantDataToSave['is_active'] = isset($variantData['is_active']) 
                        ? filter_var($variantData['is_active'], FILTER_VALIDATE_BOOLEAN)
                        : true;
                    
                    $product->variants()->create($variantDataToSave);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'محصول با موفقیت ایجاد شد',
                'data' => new ProductResource($product->fresh(['images', 'variants.color', 'variants.size', 'category']))
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

        $product->load(['images', 'variants.color', 'variants.size', 'category', 'campaigns']);

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
            // First, delete all existing images that are not in the keep list
            if ($request->has('existing_images')) {
                $keepImageIds = $request->input('existing_images', []);
                $product->images()->whereNotIn('id', $keepImageIds)->delete();
            }

            // Add new images
            if ($request->hasFile('images')) {
                $existingImagesCount = $product->images()->count();
                foreach ($request->file('images') as $index => $file) {
                    $path = $file->store('products', 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $path,
                        'sort_order' => $existingImagesCount + $index,
                    ]);
                }
            }

            // Handle variants if provided
            if ($request->has('variants')) {
                $variantIdsToKeep = [];
                $variantsInput = $request->input('variants');
                
                foreach ($variantsInput as $variantData) {
                    $variantDataToSave = [];
                    
                    // Process color - only set if provided in request
                    if (isset($variantData['color_id']) && $variantData['color_id'] !== '') {
                        $variantDataToSave['color_id'] = (int) $variantData['color_id'];
                    } elseif (!empty($variantData['color_name'])) {
                        $colorName = trim($variantData['color_name']);
                        $color = Color::firstOrCreate(
                            ['name' => $colorName],
                            [
                                'hex_code' => $variantData['color_hex_code'] ?? null,
                                'is_active' => true
                            ]
                        );
                        
                        // Update hex_code if provided and different
                        if (!empty($variantData['color_hex_code']) && $color->hex_code !== $variantData['color_hex_code']) {
                            $color->hex_code = $variantData['color_hex_code'];
                            $color->save();
                        }
                        
                        $variantDataToSave['color_id'] = $color->id;
                    }
                    
                    // Process size - only set if provided in request
                    if (isset($variantData['size_id']) && $variantData['size_id'] !== '') {
                        $variantDataToSave['size_id'] = (int) $variantData['size_id'];
                    } elseif (!empty($variantData['size_name'])) {
                        $sizeName = trim($variantData['size_name']);
                        $size = Size::firstOrCreate(
                            ['name' => $sizeName],
                            ['is_active' => true]
                        );
                        $variantDataToSave['size_id'] = $size->id;
                    }
                    
                    // Add other fields - convert to proper types
                    $variantDataToSave['price'] = isset($variantData['price']) && $variantData['price'] !== '' 
                        ? (int) $variantData['price'] 
                        : null;
                    $variantDataToSave['stock'] = isset($variantData['stock']) && $variantData['stock'] !== '' 
                        ? (int) $variantData['stock'] 
                        : 0;
                    
                    // Check if variant ID is provided (existing variant)
                    if (!empty($variantData['id'])) {
                        $variant = $product->variants()->find($variantData['id']);
                        
                        if ($variant) {
                            // Update existing variant
                            // Preserve color_id if not explicitly provided
                            if (!isset($variantDataToSave['color_id'])) {
                                $variantDataToSave['color_id'] = $variant->color_id;
                            }
                            
                            // Preserve size_id if not explicitly provided
                            if (!isset($variantDataToSave['size_id'])) {
                                $variantDataToSave['size_id'] = $variant->size_id;
                            }
                            
                            // Preserve is_active if not explicitly provided
                            if (isset($variantData['is_active'])) {
                                // Convert to boolean properly
                                $variantDataToSave['is_active'] = filter_var($variantData['is_active'], FILTER_VALIDATE_BOOLEAN);
                            } else {
                                // Keep existing is_active value
                                $variantDataToSave['is_active'] = $variant->is_active;
                            }
                            
                            $variant->update($variantDataToSave);
                            $variantIdsToKeep[] = $variant->id;
                        } else {
                            // Variant ID provided but not found, create new one
                            // For new variants, set color_id and size_id to null if not provided
                            if (!isset($variantDataToSave['color_id'])) {
                                $variantDataToSave['color_id'] = null;
                            }
                            if (!isset($variantDataToSave['size_id'])) {
                                $variantDataToSave['size_id'] = null;
                            }
                            $variantDataToSave['is_active'] = isset($variantData['is_active']) 
                                ? filter_var($variantData['is_active'], FILTER_VALIDATE_BOOLEAN)
                                : true;
                            $newVariant = $product->variants()->create($variantDataToSave);
                            $variantIdsToKeep[] = $newVariant->id;
                        }
                    } else {
                        // New variant, create it
                        // For new variants, set color_id and size_id to null if not provided
                        if (!isset($variantDataToSave['color_id'])) {
                            $variantDataToSave['color_id'] = null;
                        }
                        if (!isset($variantDataToSave['size_id'])) {
                            $variantDataToSave['size_id'] = null;
                        }
                        $variantDataToSave['is_active'] = isset($variantData['is_active']) 
                            ? filter_var($variantData['is_active'], FILTER_VALIDATE_BOOLEAN)
                            : true;
                        $newVariant = $product->variants()->create($variantDataToSave);
                        $variantIdsToKeep[] = $newVariant->id;
                    }
                }
                
                // Delete variants that are not in the request
                $product->variants()->whereNotIn('id', $variantIdsToKeep)->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'محصول با موفقیت به‌روزرسانی شد',
                'data' => new ProductResource($product->fresh(['images', 'variants.color', 'variants.size', 'category']))
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

