<?php

use App\Http\Controllers\Admin\OrderInvoiceController;
use App\Http\Controllers\Admin\DocumentationController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Storefront\Account\AddressController;
use App\Http\Controllers\Storefront\Account\DashboardController;
use App\Http\Controllers\Storefront\Account\ProfileController;
use App\Http\Controllers\Storefront\AuthController;
use App\Http\Controllers\Storefront\PasswordResetController;
use App\Http\Controllers\Storefront\ReviewController;
use App\Http\Controllers\WholesaleApplicationController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::redirect('/login', '/admin/login')->name('login');
Route::get('/locale/{locale}', LocaleController::class)->name('locale.switch');
Route::get('/sitemap.xml', [SitemapController::class, 'xml'])->name('sitemap.xml');
Route::get('/sitemap-pages.xml', [SitemapController::class, 'pages'])->name('sitemap.pages');
Route::get('/sitemap-products-{page}.xml', [SitemapController::class, 'products'])->whereNumber('page')->name('sitemap.products');
Route::get('/sitemap-categories-{page}.xml', [SitemapController::class, 'categories'])->whereNumber('page')->name('sitemap.categories');
Route::get('/sitemap-brands-{page}.xml', [SitemapController::class, 'brands'])->whereNumber('page')->name('sitemap.brands');
Route::get('/sitemap', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/accessibility', [PageController::class, 'accessibility'])->name('accessibility');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/latest-products', [ProductController::class, 'latest'])->name('products.latest');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/offers', [OfferController::class, 'index'])->name('offers.index');
Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
Route::get('/brands/{brand:slug}', [BrandController::class, 'show'])->name('brands.show');
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/returns-policy', [PageController::class, 'returns'])->name('returns');
Route::get('/shipping-policy', [PageController::class, 'shipping'])->name('shipping.policy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/contact', [ContactController::class, 'create'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store')->middleware('throttle:10,1');
Route::get('/join-us', [WholesaleApplicationController::class, 'create'])->name('join-us.create');
Route::post('/join-us', [WholesaleApplicationController::class, 'store'])->name('join-us.store')->middleware('throttle:5,1');
Route::post('/newsletter', [NewsletterController::class, 'store'])->name('newsletter.store')->middleware('throttle:6,1');

Route::redirect('/shop', '/products')->name('shop.index');
Route::redirect('/wishlist', '/favorites')->name('wishlist.index');
Route::get('/contact-us', [ContactController::class, 'create'])->name('contact.create');

Route::middleware('guest')->group(function () {
    Route::get('/account/login', [AuthController::class, 'loginForm'])->name('customer.login');
    Route::post('/account/login', [AuthController::class, 'login'])->name('customer.login.store')->middleware('throttle:10,1');
    Route::get('/account/register', [AuthController::class, 'registerForm'])->name('customer.register');
    Route::post('/account/register', [AuthController::class, 'register'])->name('customer.register.store')->middleware('throttle:10,1');
    Route::get('/account/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/account/forgot-password', [PasswordResetController::class, 'email'])->name('password.email')->middleware('throttle:5,1');
});

Route::get('/account/reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
Route::post('/account/reset-password', [PasswordResetController::class, 'update'])->name('password.update')->middleware('throttle:5,1');

Route::post('/account/logout', [AuthController::class, 'logout'])->name('customer.logout')->middleware('auth');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/update/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{product}', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');

Route::middleware('auth')->group(function () {

    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/toggle/{product}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::get('/checkout/shipping-carriers', [CheckoutController::class, 'carriers'])->name('checkout.shipping-carriers');
    Route::get('/checkout/shipping-quote', [CheckoutController::class, 'quote'])->name('checkout.shipping-quote');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store')->middleware('throttle:5,1');
    Route::get('/checkout/success/{order}', [OrderController::class, 'success'])->name('checkout.success');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('products.reviews.store');

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
    Route::get('/documentation', DocumentationController::class)->name('documentation');
    Route::get('/orders/{order}/invoice', OrderInvoiceController::class)->name('orders.invoice');
});
