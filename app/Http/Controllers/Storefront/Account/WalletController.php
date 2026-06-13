<?php

namespace App\Http\Controllers\Storefront\Account;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index(Request $request, WalletService $wallets): View
    {
        $wallet = $wallets->getOrCreateWallet($request->user());

        return view('storefront.account.wallet', [
            'wallet' => $wallet,
            'transactions' => $wallet->transactions()
                ->with(['reference', 'createdBy'])
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'creditTotal' => $wallet->transactions()->where('direction', \App\Models\WalletTransaction::DIRECTION_CREDIT)->sum('amount'),
            'debitTotal' => $wallet->transactions()->where('direction', \App\Models\WalletTransaction::DIRECTION_DEBIT)->sum('amount'),
            'depositRequests' => $request->user()->walletDepositRequests()->latest()->take(8)->get(),
            'isWholesaleAccount' => $request->user()->isWholesaleCustomer(),
        ]);
    }
}
