<?php

namespace App\Services;

use App\Models\DemandRequest;
use App\Models\Dispute;
use App\Models\HaulQuote;
use App\Models\InsuranceQuote;
use App\Models\Invoice;
use App\Models\MatchSuggestion;
use App\Models\Payment;
use App\Models\SupplyListing;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TradeService
{
    public function __construct(
        private WalletService $wallets,
        private NotificationService $notifications,
    ) {}

    public function acceptDemand(User $supplier, DemandRequest $demand, array $data): Trade
    {
        $this->assertSeller($supplier);

        return DB::transaction(function () use ($supplier, $demand, $data) {
            $demand = DemandRequest::query()->whereKey($demand->id)->lockForUpdate()->first();

            if ($demand->status !== 'active') {
                throw ValidationException::withMessages(['demand' => 'This order is no longer open.']);
            }

            $quantity = round((float) $data['quantity'], 2);
            if ($quantity <= 0 || $quantity > $demand->remaining() + 0.001) {
                throw ValidationException::withMessages([
                    'quantity' => 'Quantity must be within the '.tonnes($demand->remaining()).' still open.',
                ]);
            }

            $supply = SupplyListing::create([
                'code' => 'TMP',
                'user_id' => $supplier->id,
                'commodity_id' => $demand->commodity_id,
                'quantity' => $quantity,
                'price_per_kg' => $data['price_per_kg'],
                'moisture' => $data['moisture'] ?? $demand->moisture_max,
                'purity' => $data['purity'] ?? $demand->purity_min,
                'grade' => $data['grade'] ?? $demand->grade,
                'state' => $data['state'],
                'city' => $data['city'],
                'address' => $data['address'] ?? null,
                'status' => 'matched',
                'description' => 'Raised by accepting '.$demand->code,
            ]);
            $supply->update(['code' => $this->code('SUP', $supply->id)]);

            $demand->quantity_filled = round((float) $demand->quantity_filled + $quantity, 2);
            if ($demand->remaining() <= 0.001) {
                $demand->status = 'matched';
            }
            $demand->save();

            $trade = $this->openTrade($supply, $demand, $supplier, $demand->buyer, [
                'quantity' => $quantity,
                'price_per_kg' => $data['price_per_kg'],
                'delivery_mode' => $data['delivery_mode'],
                'logistics_cost' => $data['delivery_mode'] === 'self' ? ($data['logistics_cost'] ?? 0) : 0,
                'delivery_state' => $demand->state,
                'delivery_city' => $demand->city,
                'delivery_address' => $demand->address,
                'moisture' => $supply->moisture,
                'purity' => $supply->purity,
                'grade' => $supply->grade,
            ], 'Accepted '.$demand->code.' for '.tonnes($quantity).'.');

            return $trade;
        });
    }

    public function agreeMatch(User $actor, MatchSuggestion $match): Trade
    {
        return DB::transaction(function () use ($actor, $match) {
            $match = MatchSuggestion::query()->whereKey($match->id)->lockForUpdate()->first();
            $supply = SupplyListing::query()->whereKey($match->supply_listing_id)->lockForUpdate()->first();
            $demand = DemandRequest::query()->whereKey($match->demand_request_id)->lockForUpdate()->first();

            if ($match->status !== 'suggested' || $supply->status !== 'active' || $demand->status !== 'active') {
                throw ValidationException::withMessages(['match' => 'This pair is no longer available.']);
            }

            $allowed = $actor->isAdmin() || $actor->id === $supply->user_id || $actor->id === $demand->user_id;
            if (! $allowed) {
                abort(403);
            }

            $quantity = min((float) $supply->quantity, $demand->remaining());
            if ($quantity <= 0) {
                throw ValidationException::withMessages(['match' => 'Nothing remains to fill on this order.']);
            }

            $supply->update(['status' => 'matched', 'quantity' => $quantity]);
            $demand->quantity_filled = round((float) $demand->quantity_filled + $quantity, 2);
            if ($demand->remaining() <= 0.001) {
                $demand->status = 'matched';
            }
            $demand->save();
            $match->update(['status' => 'accepted']);

            return $this->openTrade($supply, $demand, $supply->owner, $demand->buyer, [
                'quantity' => $quantity,
                'price_per_kg' => $supply->price_per_kg,
                'delivery_mode' => 'platform',
                'logistics_cost' => 0,
                'delivery_state' => $demand->state,
                'delivery_city' => $demand->city,
                'delivery_address' => $demand->address,
                'moisture' => $supply->moisture,
                'purity' => $supply->purity,
                'grade' => $supply->grade,
            ], 'Matched '.$supply->code.' with '.$demand->code.'.');
        });
    }

    public function issueContract(Trade $trade, User $actor): Trade
    {
        if (! $actor->isAdmin() && $actor->id !== $trade->buyer_id) {
            abort(403);
        }

        return DB::transaction(function () use ($trade, $actor) {
            $trade = $this->move($trade, 'contract_issued', $actor, 'Contract issued. Invoice is now due from the buyer.');
            $this->recompute($trade);

            Invoice::query()->updateOrCreate(
                ['trade_id' => $trade->id],
                [
                    'number' => 'INV-'.str_pad((string) $trade->id, 5, '0', STR_PAD_LEFT),
                    'buyer_id' => $trade->buyer_id,
                    'supplier_id' => $trade->supplier_id,
                    'amount' => $trade->buyer_total,
                    'status' => 'issued',
                    'due_on' => now()->addDays(7)->toDateString(),
                ]
            );

            return $trade->fresh();
        });
    }

    public function acceptHaul(Trade $trade, HaulQuote $quote, User $actor): Trade
    {
        if (! $actor->isAdmin() && $actor->id !== $trade->buyer_id) {
            abort(403);
        }
        if ($quote->trade_id !== $trade->id || $quote->status !== 'submitted') {
            throw ValidationException::withMessages(['quote' => 'That haul quote cannot be accepted.']);
        }

        return DB::transaction(function () use ($trade, $quote, $actor) {
            $quote->update(['status' => 'accepted']);
            HaulQuote::query()
                ->where('trade_id', $trade->id)
                ->where('id', '!=', $quote->id)
                ->where('status', 'submitted')
                ->update(['status' => 'declined']);

            $trade->logistics_user_id = $quote->user_id;
            $trade->logistics_cost = $quote->total;
            $trade->delivery_mode = 'platform';
            $trade->save();
            $this->recompute($trade);

            return $this->move($trade, 'logistics_assigned', $actor, 'Haul awarded to '.$quote->partner->deskName().' at '.naira($quote->total).'.');
        });
    }

    public function acceptCover(Trade $trade, InsuranceQuote $quote, User $actor): Trade
    {
        if (! $actor->isAdmin() && $actor->id !== $trade->buyer_id) {
            abort(403);
        }
        if ($quote->trade_id !== $trade->id || ! in_array($quote->status, ['quoted', 'issued'], true)) {
            throw ValidationException::withMessages(['cover' => 'That cover cannot be attached.']);
        }

        return DB::transaction(function () use ($trade, $quote, $actor) {
            $policy = $quote->policy_number ?: 'POL-'.str_pad((string) $quote->id, 5, '0', STR_PAD_LEFT);
            $quote->update(['status' => 'issued', 'policy_number' => $policy]);
            InsuranceQuote::query()
                ->where('trade_id', $trade->id)
                ->where('id', '!=', $quote->id)
                ->where('status', 'quoted')
                ->update(['status' => 'declined']);

            $trade->insurer_user_id = $quote->user_id;
            $trade->insurance_premium = $quote->premium;
            $trade->save();
            $this->recompute($trade);
            $this->event($trade, $actor, $trade->status, 'Cargo cover '.$policy.' issued at '.naira($quote->premium).'.');
            $this->notifyParties($trade, 'Cover attached', $trade->code.' now carries policy '.$policy.'.');

            return $trade->fresh();
        });
    }

    public function transition(Trade $trade, string $status, User $actor, ?string $note = null): Trade
    {
        $this->assertCanMove($trade, $status, $actor);

        return $this->move($trade, $status, $actor, $note ?? status_label('trade_statuses', $status));
    }

    public function settle(Trade $trade, User $actor): Trade
    {
        if (! $actor->isAdmin() && $actor->id !== $trade->buyer_id) {
            abort(403);
        }
        if ($trade->status !== 'quality_verified') {
            throw ValidationException::withMessages(['trade' => 'Payment opens after quality is verified.']);
        }

        return DB::transaction(function () use ($trade, $actor) {
            $trade = Trade::query()->whereKey($trade->id)->lockForUpdate()->first();
            $this->recompute($trade);

            $reference = 'PAY-'.str_pad((string) $trade->id, 5, '0', STR_PAD_LEFT);
            $this->wallets->debit($trade->buyer, (float) $trade->buyer_total, 'Settlement for '.$trade->code, $reference, $trade);
            $this->wallets->credit($trade->supplier, (float) $trade->supplier_net, 'Proceeds for '.$trade->code, $reference.'-SUP', $trade);

            $admin = User::query()->where('role', 'admin')->first();
            if ($admin) {
                $this->wallets->credit(
                    $admin,
                    round((float) $trade->commission + (float) $trade->platform_fee, 2),
                    'Commission and fee on '.$trade->code,
                    $reference.'-FEE',
                    $trade
                );
            }

            Payment::create([
                'trade_id' => $trade->id,
                'invoice_id' => $trade->invoice?->id,
                'payer_id' => $trade->buyer_id,
                'payee_id' => $trade->supplier_id,
                'amount' => $trade->buyer_total,
                'status' => 'completed',
                'reference' => $reference,
                'note' => 'Wallet settlement',
            ]);

            $trade->invoice?->update(['amount' => $trade->buyer_total, 'status' => 'paid']);

            return $this->move($trade, 'payment_settled', $actor, 'Buyer wallet settled '.naira($trade->buyer_total).'. Supplier net '.naira($trade->supplier_net).'.');
        });
    }

    public function dispute(Trade $trade, User $actor, string $reason): Dispute
    {
        if (! $trade->involves($actor)) {
            abort(403);
        }
        if (in_array($trade->status, ['closed', 'disputed'], true)) {
            throw ValidationException::withMessages(['dispute' => 'This trade cannot be disputed from its current stage.']);
        }

        return DB::transaction(function () use ($trade, $actor, $reason) {
            $trade->previous_status = $trade->status;
            $trade->save();
            $this->move($trade, 'disputed', $actor, $reason, force: true);

            return Dispute::create([
                'trade_id' => $trade->id,
                'user_id' => $actor->id,
                'reason' => $reason,
                'status' => 'open',
            ]);
        });
    }

    public function resolve(Dispute $dispute, User $admin, string $outcome, string $resolution): Trade
    {
        abort_unless($admin->isAdmin(), 403);

        return DB::transaction(function () use ($dispute, $admin, $outcome, $resolution) {
            $trade = $dispute->trade;
            $dispute->update([
                'status' => 'resolved',
                'resolution' => $resolution,
                'resolved_by' => $admin->id,
                'resolved_at' => now(),
            ]);

            $next = $outcome === 'close' ? 'closed' : ($trade->previous_status ?: 'delivered');
            if (! array_key_exists($next, config('marketplace.trade_statuses'))) {
                $next = 'delivered';
            }

            return $this->move($trade, $next, $admin, 'Dispute resolved: '.$resolution, force: true);
        });
    }

    public function recompute(Trade $trade): Trade
    {
        $value = round((float) $trade->quantity * 1000 * (float) $trade->price_per_kg * (float) $trade->grade_factor, 2);
        $commission = round($value * (float) config('marketplace.commission_rate'), 2);
        $fee = (float) config('marketplace.platform_fee');

        $trade->commodity_value = $value;
        $trade->commission = $commission;
        $trade->platform_fee = $fee;
        $trade->buyer_total = round($value + (float) $trade->logistics_cost + (float) $trade->insurance_premium + $fee, 2);
        $trade->supplier_net = round($value - $commission, 2);
        $trade->save();

        if ($trade->invoice && $trade->invoice->status !== 'paid') {
            $trade->invoice->update(['amount' => $trade->buyer_total]);
        }

        return $trade;
    }

    private function openTrade(SupplyListing $supply, DemandRequest $demand, User $supplier, User $buyer, array $attrs, string $note): Trade
    {
        $trade = Trade::create([
            'code' => 'TMP',
            'supply_listing_id' => $supply->id,
            'demand_request_id' => $demand->id,
            'buyer_id' => $buyer->id,
            'supplier_id' => $supplier->id,
            'commodity_id' => $demand->commodity_id,
            'quantity' => $attrs['quantity'],
            'price_per_kg' => $attrs['price_per_kg'],
            'grade_factor' => 1,
            'moisture' => $attrs['moisture'] ?? null,
            'purity' => $attrs['purity'] ?? null,
            'grade' => $attrs['grade'] ?? null,
            'delivery_mode' => $attrs['delivery_mode'],
            'delivery_state' => $attrs['delivery_state'],
            'delivery_city' => $attrs['delivery_city'],
            'delivery_address' => $attrs['delivery_address'] ?? null,
            'logistics_cost' => $attrs['logistics_cost'] ?? 0,
            'status' => 'trade_agreed',
            'agreed_at' => now(),
        ]);
        $trade->update(['code' => $this->code('AGM', $trade->id)]);
        $this->recompute($trade);
        $this->event($trade, $supplier, 'trade_agreed', $note);
        $this->notifyParties($trade, 'Trade agreed', $trade->code.' is agreed. '.$note);

        return $trade->fresh(['buyer', 'supplier', 'commodity']);
    }

    private function move(Trade $trade, string $status, User $actor, string $note, bool $force = false): Trade
    {
        if (! $force) {
            $this->assertTransition($trade, $status);
        }

        $trade->status = $status;
        $trade->save();
        $this->event($trade, $actor, $status, $note);
        $this->notifyParties($trade, status_label('trade_statuses', $status), $trade->code.': '.$note);

        return $trade->fresh();
    }

    private function assertTransition(Trade $trade, string $status): void
    {
        $allowed = [
            'trade_agreed' => ['contract_issued'],
            'contract_issued' => $trade->delivery_mode === 'self' ? ['in_transit'] : ['logistics_assigned'],
            'logistics_assigned' => ['in_transit'],
            'in_transit' => ['delivered'],
            'delivered' => ['quality_verified'],
            'quality_verified' => ['payment_settled'],
            'payment_settled' => ['closed'],
        ];

        $next = $allowed[$trade->status] ?? [];
        if (! in_array($status, $next, true)) {
            throw ValidationException::withMessages([
                'status' => 'This trade cannot move from '.status_label('trade_statuses', $trade->status).' to '.status_label('trade_statuses', $status).'.',
            ]);
        }
    }

    private function assertCanMove(Trade $trade, string $status, User $actor): void
    {
        if ($actor->isAdmin()) {
            return;
        }

        $freight = in_array($status, ['in_transit', 'delivered'], true);
        if ($freight) {
            $allowed = ($trade->delivery_mode === 'self' && $actor->id === $trade->supplier_id)
                || ($trade->delivery_mode === 'platform' && $actor->id === $trade->logistics_user_id);
            abort_unless($allowed, 403);
            return;
        }

        if ($status === 'quality_verified' || $status === 'closed' || $status === 'contract_issued') {
            abort_unless($actor->id === $trade->buyer_id, 403);
        }
    }

    private function event(Trade $trade, ?User $actor, string $status, string $note): void
    {
        $trade->events()->create([
            'user_id' => $actor?->id,
            'status' => $status,
            'note' => $note,
        ]);
    }

    private function notifyParties(Trade $trade, string $title, string $body): void
    {
        $trade->loadMissing(['buyer', 'supplier', 'haulier', 'insurer']);
        $url = route('trades.show', $trade);
        $this->notifications->sendMany(
            array_filter([$trade->buyer, $trade->supplier, $trade->haulier, $trade->insurer, User::query()->where('role', 'admin')->first()]),
            $title,
            $body,
            $url
        );
    }

    private function assertSeller(User $user): void
    {
        if (! in_array($user->role, ['aggregator', 'farmer', 'admin'], true)) {
            abort(403, 'Only a farmer or aggregator can accept an order.');
        }
    }

    private function code(string $prefix, int $id): string
    {
        return $prefix.'-'.str_pad((string) $id, 5, '0', STR_PAD_LEFT);
    }
}
