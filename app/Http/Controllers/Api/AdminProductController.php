<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Color;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['images', 'variants.color', 'variants.size', 'category'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function show($id)
    {
        $product = Product::with(['images', 'variants.color', 'variants.size', 'category'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'has_variants' => 'boolean',
            'has_colors' => 'boolean',
            'has_sizes' => 'boolean',
            'is_active' => 'sometimes|boolean',
            'images.*' => 'sometimes|image|max:4096',
            'variants.*.color_id' => 'nullable|exists:colors,id',
            'variants.*.size_id' => 'nullable|exists:sizes,id',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'nullable|integer|min:0',
        ]);

        $data['slug'] = Str::slug($data['title']) . '-' . Str::random(4);
        
        // Convert string values to proper types for FormData
        $data['has_variants'] = $request->boolean('has_variants');
        $data['has_colors'] = $request->boolean('has_colors');
        $data['has_sizes'] = $request->boolean('has_sizes');
        $data['is_active'] = $request->boolean('is_active');
        $data['price'] = (float) $data['price'];
        $data['stock'] = (int) $data['stock'];
        
        $product = Product::create($data);

        // Handle images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('products', 'public');
                $product->images()->create([
                    'path' => $path,
                    'sort_order' => $index,
                ]);
            }
        }

        // Handle variants
        if ($request->has('variants')) {
            foreach ($request->input('variants') as $variantData) {
                $product->variants()->create($variantData);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $product->load(['images', 'variants.color', 'variants.size', 'category'])
        ]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'has_variants' => 'boolean',
            'has_colors' => 'boolean',
            'has_sizes' => 'boolean',
            'is_active' => 'sometimes|boolean',
            'images.*' => 'sometimes|image|max:4096',
            'existing_images.*' => 'sometimes|integer|exists:product_images,id',
            'variants.*.color_id' => 'nullable|exists:colors,id',
            'variants.*.size_id' => 'nullable|exists:sizes,id',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'nullable|integer|min:0',
        ]);

        // Convert string values to proper types for FormData
        $data['has_variants'] = $request->boolean('has_variants');
        $data['has_colors'] = $request->boolean('has_colors');
        $data['has_sizes'] = $request->boolean('has_sizes');
        $data['is_active'] = $request->boolean('is_active');
        $data['price'] = (float) $data['price'];
        $data['stock'] = (int) $data['stock'];
        
        $product->update($data);

        // Handle images
        // First, delete all existing images that are not in the keep list
        $keepImageIds = $request->input('existing_images', []);
        $product->images()->whereNotIn('id', $keepImageIds)->delete();

        // Add new images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('products', 'public');
                $product->images()->create([
                    'path' => $path,
                    'sort_order' => $index,
                ]);
            }
        }

        // Handle variants
        if ($request->has('variants')) {
            $product->variants()->delete();
            foreach ($request->input('variants') as $variantData) {
                $product->variants()->create($variantData);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $product->load(['images', 'variants.color', 'variants.size', 'category'])
        ]);
    }

    public function destroyImage($productId, $imageId)
    {
        $product = Product::findOrFail($productId);
        $image = $product->images()->findOrFail($imageId);

        // Delete from disk if it's a local file
        if (!str_starts_with($image->path, 'http')) {
            $filePath = storage_path('app/public/' . $image->path);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Delete from database
        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'تصویر با موفقیت حذف شد'
        ]);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'محصول با موفقیت حذف شد'
        ]);
    }
}