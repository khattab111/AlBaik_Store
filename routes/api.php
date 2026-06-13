<?php

use App\Http\Controllers\Api\Mobile\AuthController;
use App\Http\Controllers\Api\Mobile\AddressController;
use App\Http\Controllers\Api\Mobile\BrandController;
use App\Http\Controllers\Api\Mobile\CartController;
use App\Http\Controllers\Api\Mobile\CategoryController;
use App\Http\Controllers\Api\Mobile\CheckoutController;
use App\Http\Controllers\Api\Mobile\ElectronicServiceController;
use App\Http\Controllers\Api\Mobile\FavoriteController;
use App\Http\Controllers\Api\Mobile\HomeController;
use App\Http\Controllers\Api\Mobile\NotificationController;
use App\Http\Controllers\Api\Mobile\OrderController;
use App\Http\Controllers\Api\Mobile\ProductController;
use App\Http\Controllers\Api\Mobile\ProductReviewController;
use App\Http\Controllers\Api\Mobile\ProfileController;
use App\Http\Controllers\Api\Mobile\WalletController;
use App\Http\Middleware\SetMobileApiLocale;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile')
    ->name('mobile.')
    ->middleware([SetMobileApiLocale::class])
    ->group(function (): void {
        Route::prefix('auth')->name('auth.')->group(function (): void {
            Route::post('/register', [AuthController::class, 'register'])->name('register');
            Route::post('/login', [AuthController::class, 'login'])->name('login');
            Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1')->name('forgot-password');
            Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1')->name('reset-password');

            Route::middleware('auth:sanctum')->group(function (): void {
                Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
                Route::get('/me', [AuthController::class, 'me'])->name('me');
            });
        });

        Route::get('/home', HomeController::class)->name('home');
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/{slug}', [CategoryController::class, 'show'])->name('categories.show');
        Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
        Route::get('/brands/{slug}', [BrandController::class, 'show'])->name('brands.show');
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
        Route::get('/services/categories', [ElectronicServiceController::class, 'categories'])->name('services.categories');
        Route::get('/services', [ElectronicServiceController::class, 'index'])->name('services.index');
        Route::get('/services/{slug}', [ElectronicServiceController::class, 'show'])->name('services.show');

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
            Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');

            Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
            Route::post('/favorites/{product}', [FavoriteController::class, 'store'])->name('favorites.store');
            Route::delete('/favorites/{product}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');

            Route::apiResource('addresses', AddressController::class)->except(['create', 'edit']);
            Route::post('/addresses/{id}/set-default', [AddressController::class, 'setDefault'])->name('addresses.set-default');

            Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
            Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
            Route::put('/cart/items/{item}', [CartController::class, 'update'])->name('cart.items.update');
            Route::delete('/cart/items/{item}', [CartController::class, 'destroy'])->name('cart.items.destroy');
            Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

            Route::get('/checkout/summary', [CheckoutController::class, 'summary'])->name('checkout.summary');
            Route::post('/checkout/place-order', [CheckoutController::class, 'placeOrder'])->name('checkout.place-order');

            Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

            Route::post('/products/{product}/reviews', [ProductReviewController::class, 'store'])->name('products.reviews.store');

            Route::get('/wallet', [WalletController::class, 'show'])->name('wallet.show');
            Route::get('/wallet/transactions', [WalletController::class, 'transactions'])->name('wallet.transactions');
            Route::get('/wallet/deposits', [WalletController::class, 'deposits'])->name('wallet.deposits.index');
            Route::post('/wallet/deposits', [WalletController::class, 'storeDeposit'])->middleware('throttle:5,1')->name('wallet.deposits.store');

            Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
            Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
            Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
            Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

            Route::post('/services/{slug}/order', [ElectronicServiceController::class, 'storeOrder'])->middleware('throttle:10,1')->name('services.orders.store');
            Route::get('/service-orders', [ElectronicServiceController::class, 'orders'])->name('service-orders.index');
            Route::get('/service-orders/{order}', [ElectronicServiceController::class, 'order'])->name('service-orders.show');
        });
    });
