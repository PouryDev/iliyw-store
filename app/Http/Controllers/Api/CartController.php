<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AddToCartRequest;
use App\Actions\Cart\AddToCartAction;
use App\Actions\Cart\UpdateCartAction;
use App\Actions\Cart\CalculateCartTotalsAction;
use App\Exceptions\CartException;
use App\Exceptions\InsufficientStockException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    public function __construct(
        protected AddToCartAction $addToCartAction,
        protected UpdateCartAction $updateCartAction,
        protected CalculateCartTotalsAction $calculateCartTotalsAction
    ) {}

    /**
     * Get cart contents
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json($this->buildCartResponse($request));
    }

    /**
     * Add product to cart
     */
    public function add(AddToCartRequest $request, string $productSlug): JsonResponse
    {
        try {
            // Find product by slug
            $product = \App\Models\Product::where('slug', $productSlug)->first();
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'محصول یافت نشد'
                ], 404);
            }

            // Check variant requirements
            if ($product->has_variants || $product->has_colors || $product->has_sizes) {
                $hasColorSelection = !$product->has_colors || $request->color_id;
                $hasSizeSelection = !$product->has_sizes || $request->size_id;
                
                if (!$hasColorSelection || !$hasSizeSelection) {
                    return response()->json([
                        'success' => false,
                        'message' => 'برای این محصول باید رنگ و سایز را انتخاب کنید'
                    ], 400);
                }
            }

            $currentCart = $request->session()->get('cart', []);

            $updatedCart = $this->addToCartAction->execute(
                $product->id,
                $request->quantity,
                $request->color_id,
                $request->size_id,
                $currentCart
            );

            $request->session()->put('cart', $updatedCart);

            return response()->json($this->buildCartResponse($request));

        } catch (CartException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (InsufficientStockException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'key' => 'required|string',
            'quantity' => 'required|integer|min:0'
        ]);

        try {
            $currentCart = $request->session()->get('cart', []);

            if ($request->quantity === 0) {
                // Remove item
                unset($currentCart[$request->key]);
            } else {
                // Update quantity
                $currentCart = $this->updateCartAction->execute(
                    $request->key,
                    $request->quantity,
                    $currentCart
                );
            }

            $request->session()->put('cart', $currentCart);

            return response()->json($this->buildCartResponse($request));

        } catch (CartException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (InsufficientStockException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove item from cart
     */
    public function remove(Request $request, string $key): JsonResponse
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$key]);
        $request->session()->put('cart', $cart);

        return response()->json($this->buildCartResponse($request));
    }

    /**
     * Get cart summary
     */
    public function summary(Request $request): JsonResponse
    {
        return response()->json($this->buildCartResponse($request));
    }

    /**
     * Clear cart
     */
    public function clear(Request $request): JsonResponse
    {
        $request->session()->forget('cart');
        
        return response()->json($this->buildCartResponse($request));
    }

    /**
     * Build cart response
     */
    protected function buildCartResponse(Request $request): array
    {
        $cart = $request->session()->get('cart', []);

        if (empty($cart)) {
            return [
                'success' => true,
                'ok' => true,
                'items' => [],
                'total' => 0,
                'count' => 0,
                'original_total' => 0,
                'total_discount' => 0,
            ];
        }

        $totals = $this->calculateCartTotalsAction->execute($cart);

        // Format items for response
        $formattedItems = [];
        foreach ($totals['items'] as $item) {
            $product = $item['product'];
            
            $formattedItems[] = [
                'key' => $item['cart_key'],
                'product' => [
                    'id' => $product->id,
                    'title' => $product->title,
                    'price' => $product->price,
                    'slug' => $product->slug,
                    'images' => $product->images,
                ],
                'title' => $product->title,
                'price' => $item['final_price'],
                'original_price' => $item['original_price'],
                'slug' => $product->slug,
                'quantity' => $item['quantity'],
                'total' => $item['line_total'],
                'total_discount' => $item['discount_amount'],
                'campaign' => $item['campaign'] ? [
                    'id' => $item['campaign']->id,
                    'name' => $item['campaign']->name,
                    'discount_value' => $item['campaign']->discount_value,
                    'type' => $item['campaign']->type,
                ] : null,
            ];
        }

        return [
            'success' => true,
            'ok' => true,
            'items' => $formattedItems,
            'total' => $totals['subtotal'],
            'count' => $totals['total_items'],
            'original_total' => $totals['original_total'],
            'total_discount' => $totals['campaign_discount'],
        ];
    }
}
