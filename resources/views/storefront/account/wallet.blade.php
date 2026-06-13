@extends('layouts.app')

@section('title', __('My Wallet'))

@section('content')
@php
    $accountRoute = ($isWholesaleAccount ?? false) ? route('wholesale.account.dashboard') : route('account.dashboard');
    $accountLabel = ($isWholesaleAccount ?? false) ? __('Wholesale account') : __('Account');
    $statusLabels = \App\Models\Wallet::statusOptions();
    $statusClass = match ($wallet->status) {
        \App\Models\Wallet::STATUS_ACTIVE => 'bg-emerald-50 text-emerald-700',
        \App\Models\Wallet::STATUS_FROZEN => 'bg-amber-50 text-amber-700',
        \App\Models\Wallet::STATUS_DISABLED => 'bg-red-50 text-red-700',
        default => 'bg-slate-100 text-slate-600',
    };
@endphp

<section class="store-section">
    <nav class="store-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
        <a href="{{ route('home') }}" class="transition hover:text-red-700">{{ __('Home') }}</a>
        <span aria-hidden="true">›</span>
        <a href="{{ $accountRoute }}" class="transition hover:text-red-700">{{ $accountLabel }}</a>
        <span aria-hidden="true">›</span>
        <span class="text-slate-950">{{ __('My Wallet') }}</span>
    </nav>

    <div class="store-page-hero mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="store-eyebrow">{{ __('Wallet') }}</p>
            <h1 class="mt-2 text-4xl font-black leading-tight sm:text-5xl">{{ __('My Wallet') }}</h1>
            <p class="mt-3 max-w-2xl leading-7 text-slate-600">{{ __('Track your wallet balance and every wallet transaction from one secure place.') }}</p>
        </div>
        <a href="{{ $accountRoute }}" class="store-button-secondary">{{ $accountLabel }}</a>
    </div>

    <section class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_340px]">
        <div class="store-panel overflow-hidden p-0">
            <div class="bg-slate-950 p-6 text-white sm:p-8">
                <p class="text-sm font-black uppercase text-amber-300">{{ __('Available balance') }}</p>
                <div class="mt-3 flex flex-wrap items-end gap-3">
                    <strong class="text-4xl font-black sm:text-5xl">{{ number_format((float) $wallet->balance, 2) }}</strong>
                    <span class="mb-2 rounded-2xl bg-white/10 px-3 py-1 text-sm font-black">{{ $wallet->currency_code ?: __('Currency') }}</span>
                </div>
                <span class="mt-5 inline-flex rounded-full px-4 py-2 text-sm font-black {{ $statusClass }}">{{ $statusLabels[$wallet->status] ?? $wallet->status }}</span>
            </div>

            <div class="grid gap-4 p-5 sm:grid-cols-3 sm:p-6">
                <div class="rounded-3xl bg-slate-50 p-4">
                    <p class="text-sm font-bold text-slate-500">{{ __('Transactions') }}</p>
                    <p class="mt-2 text-2xl font-black text-slate-950">{{ $transactions->total() }}</p>
                </div>
                <div class="rounded-3xl bg-emerald-50 p-4">
                    <p class="text-sm font-bold text-emerald-700">{{ __('Credit') }}</p>
                    <p class="mt-2 text-2xl font-black text-emerald-700">
                        {{ number_format((float) $creditTotal, 2) }}
                    </p>
                </div>
                <div class="rounded-3xl bg-red-50 p-4">
                    <p class="text-sm font-bold text-red-700">{{ __('Debit') }}</p>
                    <p class="mt-2 text-2xl font-black text-red-700">
                        {{ number_format((float) $debitTotal, 2) }}
                    </p>
                </div>
            </div>
        </div>

        <aside class="store-panel h-fit p-5 sm:p-6">
            <h2 class="text-xl font-black">{{ __('Wallet safety') }}</h2>
            <div class="mt-4 grid gap-3 text-sm font-bold leading-7 text-slate-600">
                <p>{{ __('Wallet amounts are calculated on the server and cannot be changed from the browser.') }}</p>
                <p>{{ __('Every wallet change is stored as a transaction with a balance before and after the operation.') }}</p>
            </div>
        </aside>
    </section>

    <section class="mt-8 grid gap-6 lg:grid-cols-[380px_minmax(0,1fr)]">
        <form method="POST" action="{{ route('account.wallet.deposits.store') }}" enctype="multipart/form-data" class="store-panel grid h-fit gap-4 p-5 sm:p-6">
            @csrf
            <div>
                <p class="store-eyebrow">{{ __('Deposit') }}</p>
                <h2 class="mt-1 text-2xl font-black">{{ __('Charge wallet') }}</h2>
                <p class="mt-2 text-sm font-bold leading-6 text-slate-500">{{ __('The balance is added after admin approval.') }}</p>
            </div>
            <label class="grid gap-2">
                <span class="text-sm font-black">{{ __('Amount') }}</span>
                <input name="amount" value="{{ old('amount') }}" inputmode="decimal" class="store-field" required>
                @error('amount') <span class="text-sm font-bold text-red-600">{{ $message }}</span> @enderror
            </label>
            <label class="grid gap-2">
                <span class="text-sm font-black">{{ __('Payment method') }}</span>
                <input name="payment_method" value="{{ old('payment_method') }}" class="store-field" placeholder="{{ __('Bank transfer, cash, or wallet reference') }}">
                @error('payment_method') <span class="text-sm font-bold text-red-600">{{ $message }}</span> @enderror
            </label>
            <label class="grid gap-2">
                <span class="text-sm font-black">{{ __('Proof image') }}</span>
                <input name="proof_image" type="file" accept="image/jpeg,image/png,image/webp" class="store-field">
                @error('proof_image') <span class="text-sm font-bold text-red-600">{{ $message }}</span> @enderror
            </label>
            <button class="store-button-primary">{{ __('Submit deposit request') }}</button>
        </form>

        <section class="store-panel p-5 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="store-eyebrow">{{ __('Deposit') }}</p>
                    <h2 class="text-2xl font-black">{{ __('Deposit requests') }}</h2>
                </div>
            </div>
            <div class="mt-5 grid gap-3">
                @forelse($depositRequests as $deposit)
                    @php
                        $depositStatus = \App\Models\WalletDepositRequest::statusOptions()[$deposit->status] ?? $deposit->status;
                        $depositClass = match ($deposit->status) {
                            \App\Models\WalletDepositRequest::STATUS_APPROVED => 'bg-emerald-50 text-emerald-700',
                            \App\Models\WalletDepositRequest::STATUS_REJECTED => 'bg-red-50 text-red-700',
                            default => 'bg-amber-50 text-amber-700',
                        };
                    @endphp
                    <article class="grid gap-3 rounded-3xl border border-slate-100 p-4 sm:grid-cols-[1fr_auto_auto] sm:items-center">
                        <div>
                            <strong class="text-slate-950">{{ number_format((float) $deposit->amount, 2) }}</strong>
                            <p class="store-safe-text mt-1 text-sm font-bold text-slate-500">{{ $deposit->payment_method ?: __('Payment method') }}</p>
                            @if($deposit->admin_note)
                                <p class="store-safe-text mt-2 text-sm text-slate-600">{{ $deposit->admin_note }}</p>
                            @endif
                        </div>
                        <span class="w-fit rounded-full px-3 py-1 text-xs font-black {{ $depositClass }}">{{ $depositStatus }}</span>
                        <time class="text-sm font-bold text-slate-500" datetime="{{ $deposit->created_at?->toIso8601String() }}">{{ $deposit->created_at?->format('Y-m-d H:i') }}</time>
                    </article>
                @empty
                    <div class="rounded-3xl bg-slate-50 p-8 text-center">
                        <h3 class="text-xl font-black">{{ __('No deposit requests yet.') }}</h3>
                        <p class="mt-2 text-sm font-bold text-slate-500">{{ __('Submit a deposit request when you want to charge your wallet.') }}</p>
                    </div>
                @endforelse
            </div>
        </section>
    </section>

    <section class="store-panel mt-8 p-5 sm:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="store-eyebrow">{{ __('Transactions') }}</p>
                <h2 class="text-2xl font-black">{{ __('Wallet transactions') }}</h2>
            </div>
        </div>

        <div class="mt-5 grid gap-3">
            @forelse($transactions as $transaction)
                @php
                    $isCredit = $transaction->direction === \App\Models\WalletTransaction::DIRECTION_CREDIT;
                    $typeLabel = \App\Models\WalletTransaction::typeOptions()[$transaction->type] ?? $transaction->type;
                    $directionLabel = \App\Models\WalletTransaction::directionOptions()[$transaction->direction] ?? $transaction->direction;
                    $statusLabel = \App\Models\WalletTransaction::statusOptions()[$transaction->status] ?? $transaction->status;
                @endphp
                <article class="grid gap-4 rounded-3xl border border-slate-100 bg-white p-4 md:grid-cols-[minmax(0,1.3fr)_120px_140px_160px] md:items-center">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <strong class="store-safe-text text-slate-950">{{ $typeLabel }}</strong>
                            <span class="rounded-full px-3 py-1 text-xs font-black {{ $isCredit ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">{{ $directionLabel }}</span>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">{{ $statusLabel }}</span>
                        </div>
                        <p class="store-safe-text mt-2 text-xs font-bold text-slate-500" dir="ltr">{{ $transaction->transaction_number }}</p>
                        @if($transaction->description)
                            <p class="store-safe-text mt-2 text-sm leading-6 text-slate-600">{{ $transaction->description }}</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-500">{{ __('Amount') }}</p>
                        <p class="mt-1 font-black {{ $isCredit ? 'text-emerald-700' : 'text-red-700' }}">{{ $isCredit ? '+' : '-' }}{{ number_format((float) $transaction->amount, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-500">{{ __('Balance after') }}</p>
                        <p class="mt-1 font-black text-slate-950">{{ number_format((float) $transaction->balance_after, 2) }}</p>
                    </div>
                    <time class="text-sm font-bold text-slate-500" datetime="{{ $transaction->created_at?->toIso8601String() }}">{{ $transaction->created_at?->format('Y-m-d H:i') }}</time>
                </article>
            @empty
                <div class="rounded-3xl bg-slate-50 p-8 text-center">
                    <h3 class="text-xl font-black">{{ __('No wallet transactions yet.') }}</h3>
                    <p class="mt-2 text-sm font-bold text-slate-500">{{ __('Wallet activity will appear here after deposits, purchases, refunds, or adjustments.') }}</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $transactions->links() }}
        </div>
    </section>
</section>
@endsection
