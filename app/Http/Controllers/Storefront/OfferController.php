<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\FlashSale;
use Illuminate\Contracts\View\View;

class OfferController extends Controller
{
    public function __invoke(): View
    {
        return view('storefront.offers', [
            'flashSales' => FlashSale::with('products.images')
                ->where('is_active', true)
                ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
                ->latest()
                ->get(),
            'coupons' => Coupon::where('is_active', true)
                ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
                ->get(),
        ]);
    }
}
