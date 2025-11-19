<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Color;
use App\Models\Size;
use App\Models\ProductVariant;
use App\Services\CampaignService;

class CartController extends Controller
{
    public function __construct()
    {
        // No middleware needed for cart operations
    }

    public function index(Request $request)
    {
        return response()->json($this->buildCartSummary());
    }

    public function add(Request $request, $productSlug)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'color_id' => 'nullable|exists:colors,id',
            'size_id' => 'nullable|exists:sizes,id'
        ]);

        // Find product by slug
        $product = Product::where('slug', $productSlug)->first();
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'محصول یافت نشد'
            ], 404);
        }

        // For products with variants, require variant selection
        if ($product->has_variants || $product->has_colors || $product->has_sizes) {
            // Check if variant options are selected
            $hasColorSelection = !$product->has_colors || $request->color_id;
            $hasSizeSelection = !$product->has_sizes || $request->size_id;
            
            if (!$hasColorSelection || !$hasSizeSelection) {
                return response()->json([
                    'success' => false,
                    'message' => 'برای این محصول باید رنگ و سایز را انتخاب کنید'
                ], 400);
            }
        }
        
        // Check stock availability
        $requestedQuantity = $request->quantity;
        $availableStock = $product->stock;
        
        // If variant is selected, check variant stock
        if (($product->has_variants || $product->has_colors || $product->has_sizes) && ($request->color_id || $request->size_id)) {
            $variant = ProductVariant::where('product_id', $product->id)
                ->when($request->color_id, function ($query) use ($request) {
                    $query->where('color_id', $request->color_id);
                })
                ->when($request->size_id, function ($query) use ($request) {
                    $query->where('size_id', $request->size_id);
                })
                ->first();
                
            if (!$variant) {
                return response()->json([
                    'success' => false,
                    'message' => 'این ترکیب رنگ و سایز موجود نیست'
                ], 400);
            }
            
            $availableStock = $variant->stock;
        }

        // Check if enough stock is available
        $cart = session()->get('cart', []);
        $variantKey = ($request->color_id ?? 'no-color') . '_' . ($request->size_id ?? 'no-size');
        $key = $product->id . '_' . $variantKey;
        
        $currentQuantity = $cart[$key]['quantity'] ?? 0;
        $totalQuantity = $currentQuantity + $requestedQuantity;
        
        if ($totalQuantity > $availableStock) {
            return response()->json([
                'success' => false,
                'message' => "فقط {$availableStock} عدد موجود است"
            ], 400);
        }

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $request->quantity;
        } else {
            $cart[$key] = [
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'color_id' => $request->color_id,
                'size_id' => $request->size_id
            ];
        }

        session()->put('cart', $cart);

        return response()->json($this->buildCartSummary());
    }

    public function update(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'quantity' => 'required|integer|min:0'
        ]);

        $cart = $request->session()->get('cart', []);

        if ($request->quantity === 0) {
            unset($cart[$request->key]);
        } else {
            $cart[$request->key]['quantity'] = $request->quantity;
        }

        $request->session()->put('cart', $cart);

        return response()->json($this->buildCartSummary());
    }

    public function remove(Request $request, $key)
    {
        $cart = session()->get('cart', []);
        unset($cart[$key]);
        session()->put('cart', $cart);

        return response()->json($this->buildCartSummary());
    }

    public function summary(Request $request)
    {
        return response()->json($this->buildCartSummary());
    }

    public function clear(Request $request)
    {
        session()->forget('cart');

        return response()->json($this->buildCartSummary());
    }

    private function buildCartSummary(): array
    {
        $cart = session()->get('cart', []);
        $campaignService = new CampaignService();

        $items = [];
        $originalTotal = 0;
        $finalTotal = 0;
        $totalDiscount = 0;
        $count = 0;

        foreach ($cart as $key => $item) {
            $product = Product::with(['images', 'campaigns' => function ($query) {
                $query->where('is_active', true)
                      ->where('starts_at', '<=', now())
                      ->where('ends_at', '>=', now())
                      ->orderBy('priority', 'desc');
            }])->find($item['product_id']);
            if (!$product) {
                continue;
            }

            $quantity = (int) ($item['quantity'] ?? 0);
            $basePrice = (int) $product->price;

            // Variant display name (optional)
            $colorName = null;
            $sizeName = null;
            if (!empty($item['color_id'])) {
                $color = Color::find($item['color_id']);
                $colorName = $color?->name;
            }
            if (!empty($item['size_id'])) {
                $size = Size::find($item['size_id']);
                $sizeName = $size?->name;
            }

            $variantDisplay = null;
            if ($colorName || $sizeName) {
                $parts = [];
                if ($colorName) { $parts[] = 'رنگ ' . $colorName; }
                if ($sizeName) { $parts[] = 'سایز ' . $sizeName; }
                $variantDisplay = implode(' - ', $parts);
            }

            // Get variant stock and price if applicable
            $variantStock = null;
            $variantPrice = $basePrice;
            if ($item['color_id'] || $item['size_id']) {
                $variant = ProductVariant::where('product_id', $product->id)
                    ->when($item['color_id'], function ($query) use ($item) {
                        $query->where('color_id', $item['color_id']);
                    })
                    ->when($item['size_id'], function ($query) use ($item) {
                        $query->where('size_id', $item['size_id']);
                    })
                    ->first();
                    
                if ($variant) {
                    $variantStock = $variant->stock;
                    $variantPrice = $variant->price ?? $basePrice;
                }
            }

            // Calculate campaign discount
            $itemDiscount = 0;
            $campaign = null;
            $finalPrice = $variantPrice;
            
            if ($product->campaigns && $product->campaigns->count() > 0) {
                $campaign = $product->campaigns->first();
                $itemDiscount = $campaign->calculateDiscount($variantPrice);
                $finalPrice = $variantPrice - $itemDiscount;
            }

            $itemOriginalTotal = $variantPrice * $quantity;
            $itemFinalTotal = $finalPrice * $quantity;
            $itemTotalDiscount = $itemDiscount * $quantity;

            $items[] = [
                'key' => $key,
                'product' => [
                    'id' => $product->id,
                    'title' => $product->title,
                    'price' => $product->price,
                    'slug' => $product->slug,
                    'images' => $product->images,
                ],
                // Flattened fields for UI convenience
                'title' => $product->title,
                'price' => $finalPrice,
                'original_price' => $variantPrice,
                'slug' => $product->slug,
                'quantity' => $quantity,
                'color_id' => $item['color_id'] ?? null,
                'size_id' => $item['size_id'] ?? null,
                'variant_display_name' => $variantDisplay,
                'total' => $itemFinalTotal,
                'total_discount' => $itemTotalDiscount,
                'stock' => $variantStock ?? $product->stock,
                'campaign' => $campaign ? [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'discount_value' => $campaign->discount_value,
                    'type' => $campaign->type,
                ] : null,
            ];

            $originalTotal += $itemOriginalTotal;
            $totalDiscount += $itemTotalDiscount;
            $finalTotal += $itemFinalTotal;
            $count += $quantity;
        }

        return [
            'success' => true,
            'ok' => true,
            'items' => $items,
            'total' => $finalTotal,
            'count' => $count,
            'original_total' => $originalTotal,
            'total_discount' => $totalDiscount,
        ];
    }
}

