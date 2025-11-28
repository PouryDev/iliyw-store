<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\HeroSlideController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public API routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/campaigns/active', [CampaignController::class, 'active']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/hero-slides', [HeroSlideController::class, 'index']);
Route::post('/hero-slides/{id}/click', [HeroSlideController::class, 'click']);
// Public colors and sizes for filters
Route::get('/colors', function () {
    $colors = \App\Models\Color::where('is_active', true)->orderBy('name')->get();
    return response()->json(['success' => true, 'data' => $colors]);
});
Route::get('/sizes', function () {
    $sizes = \App\Models\Size::where('is_active', true)->orderBy('name')->get();
    return response()->json(['success' => true, 'data' => $sizes]);
});

// Search endpoint for React
Route::get('/search', [ProductController::class, 'search']);

// New search endpoint for dropdown
Route::get('/search/dropdown', [SearchController::class, 'search']);

// Delivery methods (public - needed for checkout)
Route::get('/delivery-methods', function () {
    $deliveryMethods = \App\Models\DeliveryMethod::active()->ordered()->get();
    return response()->json([
        'success' => true,
        'data' => $deliveryMethods
    ]);
});

// Auth routes (public - no authentication required)
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

// Cart routes (public - no authentication required) - with session support
Route::middleware([
    \App\Http\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
])->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::get('/cart/json', [CartController::class, 'summary']);
    Route::post('/cart/add/{product}', [CartController::class, 'add']);
    Route::put('/cart/update', [CartController::class, 'update']);
    Route::delete('/cart/remove/{cartKey}', [CartController::class, 'remove']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);
});

// Protected routes (using Sanctum for authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes that require authentication
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    
    // Order routes
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/checkout', [OrderController::class, 'checkout'])->middleware([
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
    ]);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    
    // User profile routes
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    Route::get('/user/orders', [UserController::class, 'orders']);
    Route::get('/user/stats', [UserController::class, 'stats']);
     
    // Address management
    Route::apiResource('addresses', \App\Http\Controllers\Api\AddressController::class);
    Route::post('/addresses/{address}/set-default', [\App\Http\Controllers\Api\AddressController::class, 'setDefault']);
});

// Admin API routes - use Sanctum for authentication
Route::middleware(['auth:sanctum', \App\Http\Middleware\EnsureUserIsAdmin::class])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Api\AdminDashboardController::class, 'index']);
    
    // Analytics
    Route::get('/analytics', [\App\Http\Controllers\Api\AdminAnalyticsController::class, 'index']);
    Route::get('/analytics/sales-by-day', [\App\Http\Controllers\Api\AdminAnalyticsController::class, 'salesByDay']);
    Route::get('/analytics/sales-by-hour', [\App\Http\Controllers\Api\AdminAnalyticsController::class, 'salesByHour']);
    Route::get('/analytics/top-products', [\App\Http\Controllers\Api\AdminAnalyticsController::class, 'topProducts']);
    Route::get('/analytics/top-categories', [\App\Http\Controllers\Api\AdminAnalyticsController::class, 'topCategories']);
    Route::get('/analytics/campaigns', [\App\Http\Controllers\Api\AdminAnalyticsController::class, 'campaigns']);
    Route::get('/analytics/hero-slides', [\App\Http\Controllers\Api\AdminAnalyticsController::class, 'heroSlides']);
    
    // Products
    Route::apiResource('products', \App\Http\Controllers\Api\AdminProductController::class);
    Route::delete('products/{product}/images/{image}', [\App\Http\Controllers\Api\AdminProductController::class, 'destroyImage']);
    
    // Categories
    Route::apiResource('categories', \App\Http\Controllers\Api\AdminCategoryController::class);
    Route::patch('/categories/{category}/toggle', [\App\Http\Controllers\Api\AdminCategoryController::class, 'toggle']);
    
    // Colors, Sizes
    
    Route::get('/colors', function () {
        $colors = \App\Models\Color::orderBy('name')->get();
        return response()->json(['success' => true, 'data' => $colors]);
    });
    
    Route::get('/sizes', function () {
        $sizes = \App\Models\Size::orderBy('name')->get();
        return response()->json(['success' => true, 'data' => $sizes]);
    });
    
    // Orders
    Route::get('/orders', function () {
        $orders = \App\Models\Order::with(['user', 'items.product.images', 'items.color', 'items.size', 'deliveryAddress', 'deliveryMethod'])
            ->latest()
            ->get();
        return response()->json(['success' => true, 'data' => $orders]);
    });
    
    Route::get('/orders/{order}', function ($orderId) {
        $order = \App\Models\Order::with([
            'user', 
            'items.product.images', 
            'items.color', 
            'items.size', 
            'deliveryAddress', 
            'deliveryMethod'
        ])->findOrFail($orderId);
        return response()->json(['success' => true, 'data' => $order]);
    });
    
    Route::patch('/orders/{order}/status', function (\Illuminate\Http\Request $request, $orderId) {
        $order = \App\Models\Order::findOrFail($orderId);
        
        $oldStatus = $order->status;
        $newStatus = $request->input('status');
        
        // If order is being cancelled, restore stock
        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
                $order->load('items');
                
                foreach ($order->items as $item) {
                    $quantity = $item->quantity;
                    
                    if ($item->product_variant_id) {
                        // Restore variant stock
                        $variant = \App\Models\ProductVariant::find($item->product_variant_id);
                        if ($variant) {
                            $variant->increment('stock', $quantity);
                        }
                    } else {
                        // Restore product stock
                        $product = \App\Models\Product::find($item->product_id);
                        if ($product) {
                            $product->increment('stock', $quantity);
                        }
                    }
                }
            });
        }
        
        $order->update(['status' => $newStatus]);
        return response()->json(['success' => true, 'data' => $order]);
    });
    
    // Delivery Methods
    Route::get('/delivery-methods', function () {
        $methods = \App\Models\DeliveryMethod::ordered()->get();
        return response()->json(['success' => true, 'data' => $methods]);
    });
    
    Route::post('/delivery-methods', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'fee' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);
        
        $method = \App\Models\DeliveryMethod::create($validated);
        return response()->json(['success' => true, 'data' => $method]);
    });
    
    Route::put('/delivery-methods/{method}', function (\Illuminate\Http\Request $request, $methodId) {
        $method = \App\Models\DeliveryMethod::findOrFail($methodId);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'fee' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);
        
        $method->update($validated);
        return response()->json(['success' => true, 'data' => $method]);
    });
    
    Route::delete('/delivery-methods/{method}', function ($methodId) {
        $method = \App\Models\DeliveryMethod::findOrFail($methodId);
        
        // Check if method is used in any orders
        if ($method->orders()->count() > 0) {
            return response()->json([
                'success' => false, 
                'message' => 'این روش ارسال در سفارشات استفاده شده و قابل حذف نیست'
            ], 400);
        }
        
        $method->delete();
        return response()->json(['success' => true, 'message' => 'روش ارسال با موفقیت حذف شد']);
    });

    // Discount Codes
    Route::get('/discount-codes', function () {
        $discounts = \App\Models\DiscountCode::latest()->get()->map(function ($discount) {
            $discountData = $discount->toArray();
            // Map min_order_amount to minimum_amount for frontend
            if (isset($discountData['min_order_amount'])) {
                $discountData['minimum_amount'] = $discountData['min_order_amount'];
            }
            return $discountData;
        });
        return response()->json(['success' => true, 'data' => $discounts]);
    });
    
    Route::get('/discount-codes/{id}', function ($id) {
        $discount = \App\Models\DiscountCode::findOrFail($id);
        
        // Map min_order_amount to minimum_amount for frontend
        $discountData = $discount->toArray();
        if (isset($discountData['min_order_amount'])) {
            $discountData['minimum_amount'] = $discountData['min_order_amount'];
        }
        
        return response()->json(['success' => true, 'data' => $discountData]);
    });
    
    Route::post('/discount-codes', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'code' => 'required|string|unique:discount_codes,code',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|integer|min:0',
            'minimum_amount' => 'nullable|integer|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'usage_limit' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);
        
        // Map minimum_amount to min_order_amount for database
        if (isset($data['minimum_amount'])) {
            $data['min_order_amount'] = $data['minimum_amount'];
            unset($data['minimum_amount']);
        }
        
        $discount = \App\Models\DiscountCode::create($data);
        
        // Map min_order_amount to minimum_amount for response
        $discountData = $discount->toArray();
        if (isset($discountData['min_order_amount'])) {
            $discountData['minimum_amount'] = $discountData['min_order_amount'];
        }
        
        return response()->json(['success' => true, 'data' => $discountData]);
    });
    
    Route::put('/discount-codes/{id}', function (\Illuminate\Http\Request $request, $id) {
        $discount = \App\Models\DiscountCode::findOrFail($id);
        $data = $request->validate([
            'code' => 'required|string|unique:discount_codes,code,' . $id,
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|integer|min:0',
            'minimum_amount' => 'nullable|integer|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'usage_limit' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);
        
        // Map minimum_amount to min_order_amount for database
        if (isset($data['minimum_amount'])) {
            $data['min_order_amount'] = $data['minimum_amount'];
            unset($data['minimum_amount']);
        }
        
        $discount->update($data);
        
        // Map min_order_amount to minimum_amount for response
        $discountData = $discount->fresh()->toArray();
        if (isset($discountData['min_order_amount'])) {
            $discountData['minimum_amount'] = $discountData['min_order_amount'];
        }
        
        return response()->json(['success' => true, 'data' => $discountData]);
    });
    
    Route::delete('/discount-codes/{id}', function ($id) {
        $discount = \App\Models\DiscountCode::findOrFail($id);
        $discount->delete();
        return response()->json(['success' => true, 'message' => 'کد تخفیف حذف شد']);
    });
    
    Route::patch('/discount-codes/{id}/toggle', function ($id) {
        $discount = \App\Models\DiscountCode::findOrFail($id);
        $discount->update(['is_active' => !$discount->is_active]);
        return response()->json(['success' => true, 'data' => $discount]);
    });
    
    // Campaigns
    Route::get('/campaigns', function () {
        $campaigns = \App\Models\Campaign::with('products')->latest()->get()->map(function ($campaign) {
            $campaignData = $campaign->toArray();
            // Map database fields to frontend fields
            $campaignData['title'] = $campaignData['name']; // Map name to title
            $campaignData['discount_type'] = $campaignData['type']; // Map type to discount_type
            $campaignData['expires_at'] = $campaignData['ends_at']; // Map ends_at to expires_at
            return $campaignData;
        });
        return response()->json(['success' => true, 'data' => $campaigns]);
    });
    
    Route::get('/campaigns/{id}', function ($id) {
        $campaign = \App\Models\Campaign::with('products')->findOrFail($id);
        
        // Map database fields to frontend fields
        $campaignData = $campaign->toArray();
        $campaignData['title'] = $campaignData['name']; // Map name to title
        $campaignData['discount_type'] = $campaignData['type']; // Map type to discount_type
        $campaignData['expires_at'] = $campaignData['ends_at']; // Map ends_at to expires_at
        
        return response()->json(['success' => true, 'data' => $campaignData]);
    });
    
    Route::post('/campaigns', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|integer|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'boolean',
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id'
        ]);
        
        // Map frontend fields to database fields
        $campaignData = [
            'name' => $data['title'], // Map title to name
            'description' => $data['description'],
            'type' => $data['discount_type'], // Map discount_type to type
            'discount_value' => $data['discount_value'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['expires_at'], // Map expires_at to ends_at
            'is_active' => $data['is_active'] ?? true,
        ];
        
        $campaign = \App\Models\Campaign::create($campaignData);
        $campaign->products()->attach($data['product_ids']);
        
        return response()->json(['success' => true, 'data' => $campaign->load('products')]);
    });
    
    Route::put('/campaigns/{id}', function (\Illuminate\Http\Request $request, $id) {
        $campaign = \App\Models\Campaign::findOrFail($id);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|integer|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'boolean',
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id'
        ]);
        
        // Map frontend fields to database fields
        $campaignData = [
            'name' => $data['title'], // Map title to name
            'description' => $data['description'],
            'type' => $data['discount_type'], // Map discount_type to type
            'discount_value' => $data['discount_value'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['expires_at'], // Map expires_at to ends_at
            'is_active' => $data['is_active'] ?? true,
        ];
        
        $campaign->update($campaignData);
        $campaign->products()->sync($data['product_ids']);
        
        return response()->json(['success' => true, 'data' => $campaign->load('products')]);
    });
    
    Route::delete('/campaigns/{id}', function ($id) {
        $campaign = \App\Models\Campaign::findOrFail($id);
        $campaign->delete();
        return response()->json(['success' => true, 'message' => 'کمپین حذف شد']);
    });
    
    Route::patch('/campaigns/{id}/toggle', function ($id) {
        $campaign = \App\Models\Campaign::findOrFail($id);
        $campaign->update(['is_active' => !$campaign->is_active]);
        return response()->json(['success' => true, 'data' => $campaign]);
    });
    
    // Hero Slides
    Route::apiResource('hero-slides', \App\Http\Controllers\Api\AdminHeroSlideController::class);
    Route::post('/hero-slides/update-order', [\App\Http\Controllers\Api\AdminHeroSlideController::class, 'updateOrder']);
    
    // Payment Gateways
    Route::apiResource('payment-gateways', \App\Http\Controllers\Api\AdminPaymentGatewayController::class);
    Route::patch('/payment-gateways/{paymentGateway}/toggle', [\App\Http\Controllers\Api\AdminPaymentGatewayController::class, 'toggle']);
    Route::put('/payment-gateways/{paymentGateway}/config', [\App\Http\Controllers\Api\AdminPaymentGatewayController::class, 'updateConfig']);
});

// Public payment routes
Route::get('/payment/gateways', [\App\Http\Controllers\Api\PaymentController::class, 'gateways']);

// Protected payment routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/payment/initiate', [\App\Http\Controllers\Api\PaymentController::class, 'initiate']);
    Route::post('/payment/verify', [\App\Http\Controllers\Api\PaymentController::class, 'verify']);
    Route::get('/payment/status/{transaction}', [\App\Http\Controllers\Api\PaymentController::class, 'status']);
});
