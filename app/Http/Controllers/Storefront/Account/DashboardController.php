<?php

namespace App\Http\Controllers\Storefront\Account;

use App\Http\Controllers\Controller;
use App\Models\FlashOffer;
use App\Models\Product;
use App\Services\WalletService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $isWholesaleRoute = $request->routeIs('wholesale.account.dashboard');

        if ($request->user()->isWholesaleCustomer() && ! $isWholesaleRoute) {
            return redirect()->route('wholesale.account.dashboard');
        }

        $wallet = app(WalletService::class)->getOrCreateWallet($request->user());

        return view('storefront.account.dashboard', [
            'isWholesaleAccount' => $request->user()->isWholesaleCustomer(),
            'wallet' => $wallet,
            'ordersCount' => $request->user()->orders()->count(),
            'addressesCount' => $request->user()->addresses()->count(),
            'wishlistCount' => $request->user()->wishlist()->count(),
            'latestOrders' => $request->user()->orders()->latest()->take(5)->get(),
            'wholesaleProductsCount' => Product::where('status', true)->where('is_wholesale_available', true)->count(),
            'wholesaleOffersCount' => FlashOffer::query()->currentlyValid()->forAudience(FlashOffer::AUDIENCE_WHOLESALE)->count(),
        ]);
    }
}
