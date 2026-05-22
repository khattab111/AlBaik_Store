<?php

use App\Http\Controllers\Admin\OrderInvoiceController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Storefront\Account\AddressController;
use App\Http\Controllers\Storefront\Account\DashboardController;
use App\Http\Controllers\Storefront\Account\ProfileController;
use App\Http\Controllers\Storefront\AuthController;
use App\Http\Controllers\Storefront\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::redirect('/login', '/admin/login')->name('login');
Route::get('/locale/{locale}', LocaleController::class)->name('locale.switch');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/offers', [OfferController::class, 'index'])->name('offers.index');
Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
Route::get('/brands/{brand:slug}', [BrandController::class, 'show'])->name('brands.show');
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [ContactController::class, 'create'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store')->middleware('throttle:10,1');

Route::redirect('/shop', '/products')->name('shop.index');
Route::redirect('/wishlist', '/favorites')->name('wishlist.index');
Route::get('/contact-us', [ContactController::class, 'create'])->name('contact.create');

Route::middleware('guest')->group(function () {
    Route::get('/account/login', [AuthController::class, 'loginForm'])->name('customer.login');
    Route::post('/account/login', [AuthController::class, 'login'])->name('customer.login.store')->middleware('throttle:10,1');
    Route::get('/account/register', [AuthController::class, 'registerForm'])->name('customer.register');
    Route::post('/account/register', [AuthController::class, 'register'])->name('customer.register.store')->middleware('throttle:10,1');
});

Route::post('/account/logout', [AuthController::class, 'logout'])->name('customer.logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/update/{product}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{product}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/toggle/{product}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('products.reviews.store');

    Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
    Route::patch('/cart/items/{item}', [\App\Http\Controllers\Storefront\CartController::class, 'update'])->name('cart.items.update');
    Route::delete('/cart/items/{item}', [\App\Http\Controllers\Storefront\CartController::class, 'destroy'])->name('cart.items.destroy');
    Route::post('/wishlist/{product}', [FavoriteController::class, 'toggle'])->name('wishlist.store');
    Route::delete('/wishlist/{product}', [FavoriteController::class, 'toggle'])->name('wishlist.destroy');

    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');
        Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
        Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
        Route::get('/orders', fn () => redirect()->route('orders.index'))->name('orders.index');
        Route::get('/orders/{order}', fn ($order) => redirect()->route('orders.show', $order))->name('orders.show');
    });
});

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/orders/{order}/invoice', OrderInvoiceController::class)->name('orders.invoice');
});
