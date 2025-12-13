<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    public function show($id)
    {
        $category = Category::withCount('products')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'slug' => 'nullable|string|max:255|unique:categories',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Ensure slug is unique
        $originalSlug = $data['slug'];
        $counter = 1;
        while (Category::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $data['is_active'] = $request->boolean('is_active', true);

        $category = Category::create($data);

        return response()->json([
            'success' => true,
            'data' => $category->loadCount('products')
        ]);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        // Generate slug if not provided or if name changed
        if (empty($data['slug']) || $category->name !== $data['name']) {
            $data['slug'] = Str::slug($data['name']);
            
            // Ensure slug is unique
            $originalSlug = $data['slug'];
            $counter = 1;
            while (Category::where('slug', $data['slug'])->where('id', '!=', $id)->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $data['is_active'] = $request->boolean('is_active', $category->is_active);

        $category->update($data);

        return response()->json([
            'success' => true,
            'data' => $category->fresh()->loadCount('products')
        ]);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // Check if category has products
        if ($category->products()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'نمی‌توان دسته‌بندی که محصول دارد را حذف کرد.'
            ], 400);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'دسته‌بندی با موفقیت حذف شد'
        ]);
    }

    public function toggle($id)
    {
        $category = Category::findOrFail($id);
        $category->update(['is_active' => !$category->is_active]);

        return response()->json([
            'success' => true,
            'data' => $category->fresh()->loadCount('products')
        ]);
    }
}

