<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WalletService
{
    public function getOrCreateWallet(User $user): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'currency_code' => $this->defaultCurrencyCode(),
                'status' => Wallet::STATUS_ACTIVE,
            ],
        );
    }

    public function credit(User $user, float $amount, string $type, ?Model $reference = null, ?string $description = null, array $metadata = []): WalletTransaction
    {
        return $this->record($user, $amount, $type, WalletTransaction::DIRECTION_CREDIT, $reference, $description, $metadata);
    }

    public function debit(User $user, float $amount, string $type, ?Model $reference = null, ?string $description = null, array $metadata = []): WalletTransaction
    {
        return $this->record($user, $amount, $type, WalletTransaction::DIRECTION_DEBIT, $reference, $description, $metadata);
    }

    public function refund(User $user, float $amount, ?Model $reference = null, ?string $description = null): WalletTransaction
    {
        return $this->credit($user, $amount, WalletTransaction::TYPE_REFUND, $reference, $description);
    }

    public function adjust(User $user, float $amount, string $direction, ?string $description = null): WalletTransaction
    {
        if (! in_array($direction, [WalletTransaction::DIRECTION_CREDIT, WalletTransaction::DIRECTION_DEBIT], true)) {
            throw new InvalidArgumentException('Invalid wallet adjustment direction.');
        }

        return $this->record($user, $amount, WalletTransaction::TYPE_ADJUSTMENT, $direction, null, $description, [
            'manual_adjustment' => true,
        ]);
    }

    public function canDebit(User $user, float $amount): bool
    {
        $this->assertPositiveAmount($amount);

        $wallet = $this->getOrCreateWallet($user);

        if (! $wallet->isActive()) {
            return false;
        }

        return $this->allowsNegativeBalance() || (float) $wallet->balance >= $this->normalizeAmount($amount);
    }

    public function recalculateBalance(Wallet $wallet): Wallet
    {
        return DB::transaction(function () use ($wallet): Wallet {
            $lockedWallet = Wallet::whereKey($wallet->id)->lockForUpdate()->firstOrFail();

            $balance = 0.0;

            $lockedWallet->transactions()
                ->where('status', WalletTransaction::STATUS_COMPLETED)
                ->orderBy('id')
                ->get()
                ->each(function (WalletTransaction $transaction) use (&$balance): void {
                    $amount = (float) $transaction->amount;
                    $balance += $transaction->direction === WalletTransaction::DIRECTION_CREDIT
                        ? $amount
                        : -$amount;
                });

            $lockedWallet->forceFill(['balance' => $this->normalizeAmount($balance)])->save();

            return $lockedWallet->refresh();
        });
    }

    private function record(User $user, float $amount, string $type, string $direction, ?Model $reference = null, ?string $description = null, array $metadata = []): WalletTransaction
    {
        $amount = $this->normalizeAmount($amount);
        $this->assertPositiveAmount($amount);
        $this->assertValidType($type);

        if (! in_array($direction, [WalletTransaction::DIRECTION_CREDIT, WalletTransaction::DIRECTION_DEBIT], true)) {
            throw new InvalidArgumentException('Invalid wallet transaction direction.');
        }

        return DB::transaction(function () use ($user, $amount, $type, $direction, $reference, $description, $metadata): WalletTransaction {
            $this->getOrCreateWallet($user);

            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->firstOrFail();

            if (! $wallet->isActive()) {
                throw new DomainException(__('Wallet is not active.'));
            }

            $balanceBefore = (float) $wallet->balance;
            $balanceAfter = $direction === WalletTransaction::DIRECTION_CREDIT
                ? $balanceBefore + $amount
                : $balanceBefore - $amount;

            if ($balanceAfter < 0 && ! $this->allowsNegativeBalance()) {
                throw new DomainException(__('Insufficient wallet balance'));
            }

            $balanceAfter = $this->normalizeAmount($balanceAfter);

            $wallet->forceFill(['balance' => $balanceAfter])->save();

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'type' => $type,
                'direction' => $direction,
                'amount' => $amount,
                'balance_before' => $this->normalizeAmount($balanceBefore),
                'balance_after' => $balanceAfter,
                'status' => WalletTransaction::STATUS_COMPLETED,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
                'description' => $description,
                'created_by' => Auth::id(),
                'metadata' => $metadata ?: null,
            ]);
        });
    }

    private function assertPositiveAmount(float $amount): void
    {
        if ($this->normalizeAmount($amount) <= 0) {
            throw new InvalidArgumentException(__('Wallet amount must be greater than zero.'));
        }
    }

    private function assertValidType(string $type): void
    {
        if (! array_key_exists($type, WalletTransaction::typeOptions())) {
            throw new InvalidArgumentException('Invalid wallet transaction type.');
        }
    }

    private function normalizeAmount(float $amount): float
    {
        return round($amount, 2);
    }

    private function allowsNegativeBalance(): bool
    {
        $setting = Setting::query()->where('key', 'wallet.allow_negative_balance')->first();
        $value = $setting?->value;

        if (is_array($value)) {
            $value = $value['value'] ?? false;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function defaultCurrencyCode(): ?string
    {
        return Currency::query()->where('is_default', true)->value('code');
    }
}
