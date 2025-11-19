<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class SitemapController extends Controller
{
    public function index()
    {
        $baseUrl = config('app.url');
        
        $urls = [];
        
        // Static pages
        $urls[] = [
            'loc' => $baseUrl . '/',
            'changefreq' => 'daily',
            'priority' => '1.0'
        ];
        
        // Products
        $products = Product::where('is_active', true)->get(['slug', 'updated_at']);
        foreach ($products as $product) {
            $urls[] = [
                'loc' => $baseUrl . '/product/' . $product->slug,
                'lastmod' => $product->updated_at->format('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.8'
            ];
        }
        
        // Categories (if you have category pages)
        $categories = Category::where('is_active', true)->get(['id', 'updated_at']);
        foreach ($categories as $category) {
            $urls[] = [
                'loc' => $baseUrl . '/category/' . $category->id,
                'lastmod' => $category->updated_at->format('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.7'
            ];
        }
        
        return response()->view('sitemap', compact('urls'))
            ->header('Content-Type', 'text/xml');
    }
}
