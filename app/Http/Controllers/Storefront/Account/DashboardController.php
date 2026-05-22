<?php

namespace App\Http\Controllers\Storefront\Account;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('storefront.account.dashboard', [
            'ordersCount' => $request->user()->orders()->count(),
            'addressesCount' => $request->user()->addresses()->count(),
            'wishlistCount' => $request->user()->wishlist()->count(),
            'latestOrders' => $request->user()->orders()->latest()->take(5)->get(),
        ]);
    }
}
