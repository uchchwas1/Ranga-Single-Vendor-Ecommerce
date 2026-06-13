<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\Auth\SocialAuthController;
use App\Http\Controllers\Api\V1\Auth\TwoFactorController;
use App\Http\Controllers\Api\V1\Catalogue\BrandController;
use App\Http\Controllers\Api\V1\Catalogue\CategoryController;
use App\Http\Controllers\Api\V1\Catalogue\ProductController;
use App\Http\Controllers\Api\V1\Catalogue\SearchController;
use App\Http\Controllers\Api\V1\Commerce\CartController;
use App\Http\Controllers\Api\V1\Commerce\CheckoutController;
use App\Http\Controllers\Api\V1\Commerce\CompareController;
use App\Http\Controllers\Api\V1\Commerce\OrderController;
use App\Http\Controllers\Api\V1\Commerce\WishlistController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\SettingsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    // Public settings (white-label bootstrap for storefront / mobile apps)
    Route::get('/settings', SettingsController::class)
        ->middleware('throttle:api')
        ->name('settings.public');

    // Product comparison — registered before /products/{slug} so the
    // "compare" segment is not captured as a product slug.
    Route::middleware('throttle:api')->group(function (): void {
        Route::get('/products/compare', [CompareController::class, 'index'])->name('products.compare.index');
        Route::post('/products/compare/{product}', [CompareController::class, 'store'])->name('products.compare.store');
        Route::delete('/products/compare/{product}', [CompareController::class, 'destroy'])->name('products.compare.destroy');
    });

    // Public catalogue (browsing, search) — open to guests and apps.
    Route::middleware('throttle:api')->group(function (): void {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
        Route::get('/products/{slug}/variants', [ProductController::class, 'variants'])->name('products.variants');

        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/{slug}/products', [CategoryController::class, 'products'])->name('categories.products');

        Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
        Route::get('/brands/{slug}/products', [BrandController::class, 'products'])->name('brands.products');
    });

    // Search has a tighter rate limit (security checklist: search 30/min).
    Route::middleware('throttle:search')->group(function (): void {
        Route::get('/search', [SearchController::class, 'index'])->name('search.index');
        Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');
    });

    // Cart & checkout — guests (cart token) and authenticated users.
    Route::middleware('throttle:api')->group(function (): void {
        Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
        Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');
        Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
        Route::put('/cart/items/{item}', [CartController::class, 'update'])->name('cart.items.update');
        Route::delete('/cart/items/{item}', [CartController::class, 'destroy'])->name('cart.items.destroy');
        Route::post('/cart/items/{item}/save-for-later', [CartController::class, 'saveForLater'])->name('cart.items.save');
        Route::post('/cart/saved/{saved}/move', [CartController::class, 'moveSaved'])->name('cart.saved.move');

        Route::get('/checkout/shipping-methods', [CheckoutController::class, 'shippingMethods'])->name('checkout.shipping');
        Route::post('/checkout', [CheckoutController::class, 'place'])->name('checkout.place');
        Route::match(['get', 'post'], '/checkout/payment/{gateway}/callback', [CheckoutController::class, 'callback'])
            ->name('checkout.callback');
    });

    Route::prefix('auth')->name('auth.')->group(function (): void {
        Route::post('/register', RegisterController::class)
            ->middleware('throttle:auth')
            ->name('register');

        Route::post('/login', LoginController::class)
            ->middleware('throttle:auth')
            ->name('login');

        Route::post('/social/{provider}', SocialAuthController::class)
            ->middleware('throttle:auth')
            ->name('social');

        Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])
            ->middleware('throttle:auth')
            ->name('2fa.verify');

        Route::post('/forgot-password', ForgotPasswordController::class)
            ->middleware('throttle:password-reset')
            ->name('password.email');

        Route::post('/reset-password', ResetPasswordController::class)
            ->middleware('throttle:password-reset')
            ->name('password.update');

        Route::get('/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
            ->middleware(['signed', 'throttle:auth'])
            ->name('verification.verify');

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::post('/logout', LogoutController::class)->name('logout');

            Route::post('/verify-email/resend', [EmailVerificationController::class, 'resend'])
                ->middleware('throttle:auth')
                ->name('verification.resend');

            Route::post('/2fa/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable');
            Route::post('/2fa/confirm', [TwoFactorController::class, 'confirm'])->name('2fa.confirm');
            Route::post('/2fa/disable', [TwoFactorController::class, 'disable'])->name('2fa.disable');
        });
    });

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        // Wishlist
        Route::get('/profile/wishlist', [WishlistController::class, 'index'])->name('profile.wishlist.index');
        Route::post('/profile/wishlist/{product}', [WishlistController::class, 'store'])->name('profile.wishlist.store');
        Route::delete('/profile/wishlist/{product}', [WishlistController::class, 'destroy'])->name('profile.wishlist.destroy');

        // Order history + detail
        Route::get('/profile/orders', [OrderController::class, 'index'])->name('profile.orders.index');
        Route::get('/orders/{order_number}', [OrderController::class, 'show'])->name('orders.show');
    });

    Route::prefix('admin')->name('admin.')
        ->middleware(['auth:sanctum', 'verified', 'throttle:api'])
        ->group(function (): void {
            Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
            Route::put('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
        });
});
