<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletService
{
    public function credit(User $user, float $amount, string $narration, string $reference, ?Model $related = null): WalletTransaction
    {
        return $this->move($user, $amount, 'credit', $narration, $reference, $related);
    }

    public function debit(User $user, float $amount, string $narration, string $reference, ?Model $related = null): WalletTransaction
    {
        return $this->move($user, $amount, 'debit', $narration, $reference, $related);
    }

    private function move(User $user, float $amount, string $type, string $narration, string $reference, ?Model $related): WalletTransaction
    {
        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'Amount must be greater than zero.']);
        }

        return DB::transaction(function () use ($user, $amount, $type, $narration, $reference, $related) {
            $wallet = Wallet::query()->where('user_id', $user->id)->lockForUpdate()->first();

            if (! $wallet) {
                $wallet = $user->wallet()->create(['balance' => 0, 'currency' => 'NGN']);
                $wallet = Wallet::query()->whereKey($wallet->id)->lockForUpdate()->first();
            }

            $balance = (float) $wallet->balance;

            if ($type === 'debit' && $balance + 0.001 < $amount) {
                throw ValidationException::withMessages([
                    'wallet' => 'The wallet does not have enough to cover '.naira($amount).'. Available balance is '.naira($balance).'.',
                ]);
            }

            $next = $type === 'debit' ? $balance - $amount : $balance + $amount;
            $wallet->update(['balance' => round($next, 2)]);

            return $wallet->transactions()->create([
                'type' => $type,
                'amount' => round($amount, 2),
                'balance_after' => round($next, 2),
                'reference' => $reference,
                'narration' => $narration,
                'status' => 'completed',
                'related_type' => $related?->getMorphClass(),
                'related_id' => $related?->getKey(),
            ]);
        });
    }
}
