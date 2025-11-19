<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\ShopController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\DiscountCodeController as AdminDiscountCodeController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ColorController as AdminColorController;
use App\Http\Controllers\Admin\SizeController as AdminSizeController;
use App\Http\Controllers\Admin\ProductVariantController as AdminProductVariantController;
use App\Http\Controllers\Admin\DeliveryMethodController as AdminDeliveryMethodController;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountController;
use Illuminate\Support\Facades\Auth;

// Test session route
Route::get('/test-session', function () {
    return response()->json([
        'session_id' => session()->getId(),
        'is_authenticated' => Auth::check(),
        'user_id' => Auth::id(),
        'user' => Auth::user(),
    ]);
});

// React app routes
Route::get('/checkout', function () {
    return view('react-app');
})->name('checkout.index');
Route::get('/account', function () {
    return view('react-app');
})->name('account.index');
Route::get('/account/*', function () {
    return view('react-app');
});

// Auth routes (legacy - not used by React app)
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

// Admin area (React app)
Route::middleware([EnsureUserIsAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return view('react-app');
    })->name('dashboard');
    Route::get('/*', function () {
        return view('react-app');
    });
});

// Account API routes (for React to use)
Route::middleware('auth')->group(function () {
    Route::post('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::post('/account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');
});

// Payment callback route (for gateway redirects)
Route::get('/payment/callback/{gateway}', [\App\Http\Controllers\Api\PaymentController::class, 'callback'])
    ->name('payment.callback');

// React SPA route - catch all for frontend routes (must be last)
Route::get('/{any}', function () {
    return view('react-app');
})->where('any', '.*')->name('react-app');
