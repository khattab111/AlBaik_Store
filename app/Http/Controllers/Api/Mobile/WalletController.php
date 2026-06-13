<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Api\Mobile\Concerns\RespondsToMobile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Mobile\StoreWalletDepositRequest;
use App\Http\Resources\Api\Mobile\WalletDepositRequestResource;
use App\Http\Resources\Api\Mobile\WalletResource;
use App\Http\Resources\Api\Mobile\WalletTransactionResource;
use App\Models\WalletDepositRequest;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    use RespondsToMobile;

    public function show(Request $request, WalletService $wallets): JsonResponse
    {
        return $this->success(new WalletResource($wallets->getOrCreateWallet($request->user())));
    }

    public function transactions(Request $request, WalletService $wallets): JsonResponse
    {
        $wallet = $wallets->getOrCreateWallet($request->user());

        $transactions = $wallet->transactions()
            ->latest('id')
            ->paginate((int) $request->input('per_page', 15));

        return $this->success($this->paginated($transactions, WalletTransactionResource::class));
    }

    public function deposits(Request $request): JsonResponse
    {
        $deposits = WalletDepositRequest::query()
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->paginate((int) $request->input('per_page', 15));

        return $this->success($this->paginated($deposits, WalletDepositRequestResource::class));
    }

    public function storeDeposit(StoreWalletDepositRequest $request): JsonResponse
    {
        $proofPath = $request->hasFile('proof_image')
            ? $request->file('proof_image')->store('wallet-deposits', 'public')
            : null;

        $deposit = WalletDepositRequest::create([
            'user_id' => $request->user()->id,
            'amount' => round((float) $request->input('amount'), 2),
            'payment_method' => $request->input('payment_method'),
            'proof_image' => $proofPath,
            'status' => WalletDepositRequest::STATUS_PENDING,
        ]);

        return $this->success(
            new WalletDepositRequestResource($deposit),
            __('Wallet deposit request submitted and waiting for approval.'),
            201
        );
    }
}
