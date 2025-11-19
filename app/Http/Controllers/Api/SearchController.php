<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $searchQuery = $request->input('q', '');
        $limit = $request->input('limit', 10);

        if (empty(trim($searchQuery))) {
            return response()->json([
                'success' => true,
                'data' => [
                    'products' => [],
                    'categories' => []
                ]
            ]);
        }

        // Search products
        $products = Product::query()
            ->with(['images', 'campaigns' => function ($query) {
                $query->where('is_active', true)
                      ->where('starts_at', '<=', now())
                      ->where('ends_at', '>=', now())
                      ->orderBy('priority', 'desc');
            }])
            ->where(function ($query) use ($searchQuery) {
                $query->where('title', 'like', "%{$searchQuery}%")
                      ->orWhere('description', 'like', "%{$searchQuery}%");
            })
            ->where('is_active', true)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'slug' => $product->slug,
                    'title' => $product->title,
                    'price' => $product->price,
                    'image' => $product->images->first()?->url ?? '/images/placeholder.jpg',
                    'url' => "/product/{$product->slug}",
                    'type' => 'product'
                ];
            });

        // Search categories
        $categories = Category::query()
            ->where(function ($query) use ($searchQuery) {
                $query->where('name', 'like', "%{$searchQuery}%")
                      ->orWhere('description', 'like', "%{$searchQuery}%");
            })
            ->where('is_active', true)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'url' => "/category/{$category->id}",
                    'type' => 'category'
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'products' => $products,
                'categories' => $categories
            ]
        ]);
    }
}
