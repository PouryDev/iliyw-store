<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CampaignService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        return view('shop.cart', compact('cart'));
    }

    public function add(Request $request, Product $product)
    {
        $quantity = max(1, (int) $request->integer('quantity', 1));
        $colorId = $request->input('color_id');
        $sizeId = $request->input('size_id');
        
        // Create cart key based on product and variant
        $cartKey = $product->id;
        if ($colorId || $sizeId) {
            $cartKey .= '_' . ($colorId ?: '0') . '_' . ($sizeId ?: '0');
        }
        
        $cart = $request->session()->get('cart', []);
        $cart[$cartKey] = ($cart[$cartKey] ?? 0) + $quantity;
        $request->session()->put('cart', $cart);
        
        if ($request->expectsJson()) {
            return response()->json($this->buildCartPayload($request));
        }
        return redirect()->route('cart.index');
    }

    public function remove(Request $request, $cartKey)
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$cartKey]);
        $request->session()->put('cart', $cart);
        if ($request->expectsJson()) {
            return response()->json($this->buildCartPayload($request));
        }
        return redirect()->route('cart.index');
    }

    private function buildCartPayload(Request $request): array
    {
        $cart = $request->session()->get('cart', []);
        $count = array_sum($cart);
        $items = [];
        $campaignService = new CampaignService();
        
        foreach ($cart as $cartKey => $qty) {
            // Parse cart key to get product ID and variant info
            $parts = explode('_', $cartKey);
            $productId = $parts[0];
            $colorId = isset($parts[1]) && $parts[1] !== '0' ? $parts[1] : null;
            $sizeId = isset($parts[2]) && $parts[2] !== '0' ? $parts[2] : null;
            
            $product = Product::find($productId);
            if ($product) {
                $variantDisplayName = null;
                $unitPrice = $product->price;
                $originalPrice = $product->price;
                $campaignDiscount = 0;
                $campaign = null;
                
                // Find variant info if exists
                if ($colorId || $sizeId) {
                    $productVariant = ProductVariant::where('product_id', $productId)
                        ->when($colorId, function ($query) use ($colorId) {
                            $query->where('color_id', $colorId);
                        })
                        ->when($sizeId, function ($query) use ($sizeId) {
                            $query->where('size_id', $sizeId);
                        })
                        ->first();
                    
                    if ($productVariant) {
                        $variantDisplayName = $productVariant->display_name;
                        $originalPrice = $productVariant->price ?? $product->price;
                        
                        // Calculate campaign discount for variant
                        $campaignData = $campaignService->calculateVariantPrice($productVariant);
                        $unitPrice = $campaignData['campaign_price'];
                        $campaignDiscount = $campaignData['discount_amount'];
                        $campaign = $campaignData['campaign'];
                    }
                } else {
                    // Calculate campaign discount for product
                    $campaignData = $campaignService->calculateProductPrice($product);
                    $unitPrice = $campaignData['campaign_price'];
                    $campaignDiscount = $campaignData['discount_amount'];
                    $campaign = $campaignData['campaign'];
                }
                
                $items[] = [
                    'cart_key' => $cartKey,
                    'id' => $product->id,
                    'slug' => $product->slug,
                    'title' => $product->title,
                    'variant_display_name' => $variantDisplayName,
                    'original_price' => $originalPrice,
                    'price' => $unitPrice,
                    'campaign_discount' => $campaignDiscount,
                    'campaign' => $campaign ? [
                        'id' => $campaign->id,
                        'name' => $campaign->name,
                        'discount_type' => $campaign->discount_type,
                        'discount_value' => $campaign->discount_value,
                    ] : null,
                    'quantity' => $qty,
                    'total' => $unitPrice * $qty,
                    'total_discount' => $campaignDiscount * $qty,
                ];
            }
        }
        
        $total = array_sum(array_column($items, 'total'));
        $totalDiscount = array_sum(array_column($items, 'total_discount'));
        $originalTotal = $total + $totalDiscount;
        
        return [
            'ok' => true, 
            'count' => $count, 
            'items' => $items, 
            'total' => $total,
            'original_total' => $originalTotal,
            'total_discount' => $totalDiscount,
        ];
    }

    public function summary(Request $request)
    {
        return response()->json($this->buildCartPayload($request));
    }
}


