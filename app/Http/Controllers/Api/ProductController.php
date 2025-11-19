<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->input('q');
        $page = $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 12);
        $categoryId = $request->input('category_id');
        $sort = $request->input('sort'); // 'newest' | 'cheapest' | 'priciest' | 'best_seller' | 'random'
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');
        $colors = $request->filled('colors') ? collect(explode(',', (string) $request->input('colors')))->filter()->values() : collect();
        $sizes = $request->filled('sizes') ? collect(explode(',', (string) $request->input('sizes')))->filter()->values() : collect();

        $query = Product::query()
            ->with(['images', 'campaigns' => function ($query) {
                $query->where('is_active', true)
                      ->where('starts_at', '<=', now())
                      ->where('ends_at', '>=', now())
                      ->orderBy('priority', 'desc');
            }])
            ->when($searchQuery, function ($queryBuilder) use ($searchQuery) {
                $queryBuilder->where(function ($q2) use ($searchQuery) {
                    $q2->where('title', 'like', "%{$searchQuery}%")
                       ->orWhere('description', 'like', "%{$searchQuery}%");
                });
            })
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            })
            // Price range filter: consider product price OR any active variant price
            ->when($minPrice, function ($q) use ($minPrice) {
                $q->where(function ($q2) use ($minPrice) {
                    $q2->where('price', '>=', (int) $minPrice)
                        ->orWhereExists(function ($sub) use ($minPrice) {
                            $sub->selectRaw('1')
                                ->from('product_variants as pv')
                                ->whereColumn('pv.product_id', 'products.id')
                                ->where('pv.is_active', true)
                                ->whereRaw('COALESCE(pv.price, products.price) >= ?', [(int) $minPrice]);
                        });
                });
            })
            ->when($maxPrice, function ($q) use ($maxPrice) {
                $q->where(function ($q2) use ($maxPrice) {
                    $q2->where('price', '<=', (int) $maxPrice)
                        ->orWhereExists(function ($sub) use ($maxPrice) {
                            $sub->selectRaw('1')
                                ->from('product_variants as pv')
                                ->whereColumn('pv.product_id', 'products.id')
                                ->where('pv.is_active', true)
                                ->whereRaw('COALESCE(pv.price, products.price) <= ?', [(int) $maxPrice]);
                        });
                });
            })
            // Color filter: must have at least one active variant with color in list
            ->when($colors->isNotEmpty(), function ($q) use ($colors) {
                $q->whereExists(function ($sub) use ($colors) {
                    $sub->selectRaw('1')
                        ->from('product_variants as pv')
                        ->whereColumn('pv.product_id', 'products.id')
                        ->where('pv.is_active', true)
                        ->whereIn('pv.color_id', $colors->all());
                });
            })
            // Size filter: must have at least one active variant with size in list
            ->when($sizes->isNotEmpty(), function ($q) use ($sizes) {
                $q->whereExists(function ($sub) use ($sizes) {
                    $sub->selectRaw('1')
                        ->from('product_variants as pv')
                        ->whereColumn('pv.product_id', 'products.id')
                        ->where('pv.is_active', true)
                        ->whereIn('pv.size_id', $sizes->all());
                });
            })
            ->where('is_active', true)
            ;

        // Sorting rules
        if ($sort === 'random') {
            $query->inRandomOrder();
        } elseif ($sort === 'cheapest') {
            // Order by min price considering variants
            $minVariantPriceSub = \DB::table('product_variants as pv')
                ->selectRaw('MIN(COALESCE(pv.price, products.price))')
                ->whereColumn('pv.product_id', 'products.id')
                ->where('pv.is_active', true);
            $subSql = $minVariantPriceSub->toSql();
            $bindings = $minVariantPriceSub->getBindings();
            $query->orderByRaw('LEAST(products.price, COALESCE((' . $subSql . '), products.price)) asc', $bindings);
        } elseif ($sort === 'priciest') {
            // Order by max price considering variants
            $maxVariantPriceSub = \DB::table('product_variants as pv')
                ->selectRaw('MAX(COALESCE(pv.price, products.price))')
                ->whereColumn('pv.product_id', 'products.id')
                ->where('pv.is_active', true);
            $subSql = $maxVariantPriceSub->toSql();
            $bindings = $maxVariantPriceSub->getBindings();
            $query->orderByRaw('GREATEST(products.price, COALESCE((' . $subSql . '), products.price)) desc', $bindings);
        } elseif ($sort === 'best_seller') {
            // Order by sold quantity aggregated from order_items
            $soldSub = \DB::table('order_items as oi')
                ->selectRaw('oi.product_id, SUM(oi.quantity) as sold_qty')
                ->groupBy('oi.product_id');
            $query->leftJoinSub($soldSub, 'sales', function ($join) {
                $join->on('sales.product_id', '=', 'products.id');
            })
            ->select('products.*')
            ->orderByRaw('COALESCE(sales.sold_qty, 0) desc')
            ->latest('products.id');
        } else {
            // newest (default)
            $query->latest();
        }

        $products = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'has_more_pages' => $products->hasMorePages(),
            ]
        ]);
    }

    public function show(Product $product)
    {
        $product->load(['images', 'activeVariants.color', 'activeVariants.size', 'campaigns']);
        
        // Add available_colors and available_sizes attributes
        $product->setAppends(['available_colors', 'available_sizes', 'total_stock']);
        
        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    public function search(Request $request)
    {
        $searchQuery = $request->input('q');
        $page = $request->input('page', 1);
        $perPage = 12;

        $products = Product::query()
            ->with(['images', 'campaigns' => function ($query) {
                $query->where('is_active', true)
                      ->where('starts_at', '<=', now())
                      ->where('ends_at', '>=', now())
                      ->orderBy('priority', 'desc');
            }])
            ->when($searchQuery, function ($queryBuilder) use ($searchQuery) {
                $queryBuilder->where(function ($q2) use ($searchQuery) {
                    $q2->where('title', 'like', "%{$searchQuery}%")
                       ->orWhere('description', 'like', "%{$searchQuery}%");
                });
            })
            ->where('is_active', true)
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => $products->items(),
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
