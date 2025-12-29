<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ColorController;
use App\Http\Controllers\Api\SizeController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\HeroSlideController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\DeliveryMethodController;
use App\Http\Controllers\Api\Admin;

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

// ====================================================================
// PUBLIC API ROUTES
// ====================================================================

// Products
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/search', [ProductController::class, 'search']);
Route::get('/search/dropdown', [SearchController::class, 'search']);

// Campaigns
Route::get('/campaigns/active', [CampaignController::class, 'active']);

// Categories
Route::get('/categories', [CategoryController::class, 'index']);

// Colors & Sizes (for filters)
Route::get('/colors', [ColorController::class, 'index']);
Route::get('/sizes', [SizeController::class, 'index']);

// Delivery Methods (public - needed for checkout)
Route::get('/delivery-methods', [DeliveryMethodController::class, 'index']);

// Hero Slides
Route::get('/hero-slides', [HeroSlideController::class, 'index']);
Route::post('/hero-slides/{id}/click', [HeroSlideController::class, 'click']);

// Payment Gateways (public)
Route::get('/payment/gateways', [PaymentController::class, 'gateways']);

// Auth routes (public - no authentication required)
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

// ====================================================================
// CART & CHECKOUT ROUTES (with session support)
// ====================================================================

Route::middleware([
    \App\Http\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
])->group(function () {
    // Cart routes (public - no authentication required)
    Route::get('/cart', [CartController::class, 'index']);
    Route::get('/cart/json', [CartController::class, 'summary']);
    Route::post('/cart/add/{product}', [CartController::class, 'add']);
    Route::put('/cart/update', [CartController::class, 'update']);
    Route::delete('/cart/remove/{cartKey}', [CartController::class, 'remove']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);
    
    // Order notification route (public, uses session)
    Route::post('/orders/send-notification', [OrderController::class, 'sendNotification']);
});

// ====================================================================
// PROTECTED ROUTES (authenticated users)
// ====================================================================

Route::middleware('auth:sanctum')->group(function () {
    // Auth routes that require authentication
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    
    // Order routes
    Route::get('/orders', [OrderController::class, 'index']);
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
    Route::apiResource('addresses', AddressController::class);
    Route::post('/addresses/{address}/set-default', [AddressController::class, 'setDefault']);
    
    // Payment routes (protected)
    Route::post('/payment/initiate', [PaymentController::class, 'initiate']);
    Route::post('/payment/verify', [PaymentController::class, 'verify']);
    Route::get('/payment/status/{transaction}', [PaymentController::class, 'status']);
});

// ====================================================================
// ADMIN API ROUTES (authenticated + admin middleware)
// ====================================================================

Route::middleware(['auth:sanctum', \App\Http\Middleware\EnsureUserIsAdmin::class])
    ->prefix('admin')
    ->group(function () {
        
        // Dashboard & Analytics
        Route::get('/dashboard', [\App\Http\Controllers\Api\AdminDashboardController::class, 'index']);
        Route::get('/analytics', [\App\Http\Controllers\Api\AdminAnalyticsController::class, 'index']);
        Route::get('/analytics/sales-by-day', [\App\Http\Controllers\Api\AdminAnalyticsController::class, 'salesByDay']);
        Route::get('/analytics/sales-by-hour', [\App\Http\Controllers\Api\AdminAnalyticsController::class, 'salesByHour']);
        Route::get('/analytics/top-products', [\App\Http\Controllers\Api\AdminAnalyticsController::class, 'topProducts']);
        Route::get('/analytics/top-categories', [\App\Http\Controllers\Api\AdminAnalyticsController::class, 'topCategories']);
        Route::get('/analytics/campaigns', [\App\Http\Controllers\Api\AdminAnalyticsController::class, 'campaigns']);
        Route::get('/analytics/hero-slides', [\App\Http\Controllers\Api\AdminAnalyticsController::class, 'heroSlides']);
        
        // Products (Admin)
        Route::apiResource('products', Admin\ProductController::class);
        Route::delete('products/{product}/images/{image}', [\App\Http\Controllers\Api\AdminProductController::class, 'destroyImage']);
        
        // Categories (Admin)
        Route::apiResource('categories', Admin\CategoryController::class);
        Route::patch('/categories/{category}/toggle', [\App\Http\Controllers\Api\AdminCategoryController::class, 'toggle']);
        
        // Orders (Admin)
        Route::apiResource('orders', Admin\OrderController::class)->only(['index', 'show', 'destroy']);
        Route::patch('/orders/{order}/status', [Admin\OrderController::class, 'updateStatus']);
        Route::post('/orders/{order}/cancel', [Admin\OrderController::class, 'cancel']);
        
        // Campaigns (Admin)
        Route::apiResource('campaigns', Admin\CampaignController::class);
        
        // Discount Codes (Admin)
        Route::apiResource('discount-codes', Admin\DiscountCodeController::class);
        
        // Delivery Methods (Admin)
        Route::apiResource('delivery-methods', Admin\DeliveryMethodController::class);
        
        // Colors & Sizes (Admin)
        Route::apiResource('colors', Admin\ColorController::class);
        Route::apiResource('sizes', Admin\SizeController::class);
        
        // Hero Slides (Admin)
        Route::apiResource('hero-slides', \App\Http\Controllers\Api\AdminHeroSlideController::class);
        Route::post('/hero-slides/update-order', [\App\Http\Controllers\Api\AdminHeroSlideController::class, 'updateOrder']);
        
        // Payment Gateways (Admin)
        Route::apiResource('payment-gateways', \App\Http\Controllers\Api\AdminPaymentGatewayController::class);
        Route::patch('/payment-gateways/{paymentGateway}/toggle', [\App\Http\Controllers\Api\AdminPaymentGatewayController::class, 'toggle']);
        Route::put('/payment-gateways/{paymentGateway}/config', [\App\Http\Controllers\Api\AdminPaymentGatewayController::class, 'updateConfig']);
    });
