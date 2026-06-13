<?php

namespace App\Http\Controllers\Storefront\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\WalletDepositRequest as StoreWalletDepositRequest;
use App\Models\WalletDepositRequest;
use Illuminate\Http\RedirectResponse;

class WalletDepositController extends Controller
{
    public function store(StoreWalletDepositRequest $request): RedirectResponse
    {
        $proofPath = $request->hasFile('proof_image')
            ? $request->file('proof_image')->store('wallet-deposits', 'public')
            : null;

        WalletDepositRequest::create([
            'user_id' => $request->user()->id,
            'amount' => round((float) $request->input('amount'), 2),
            'payment_method' => $request->input('payment_method'),
            'proof_image' => $proofPath,
            'status' => WalletDepositRequest::STATUS_PENDING,
        ]);

        return redirect()->route('account.wallet.index')->with('status', __('Wallet deposit request submitted and waiting for approval.'));
    }
}
