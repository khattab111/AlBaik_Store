<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\FlashOfferService;
use Illuminate\Contracts\View\View;

class OfferController extends Controller
{
    public function __invoke(FlashOfferService $flashOffers): View
    {
        return view('storefront.offers', [
            'flashOffers' => $flashOffers->getActiveOffers(),
            'coupons' => Coupon::where('is_active', true)
                ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
                ->get(),
        ]);
    }
}
