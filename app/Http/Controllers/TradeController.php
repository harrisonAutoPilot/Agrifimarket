<?php

namespace App\Http\Controllers;

use App\Models\HaulQuote;
use App\Models\InsuranceQuote;
use App\Models\MatchSuggestion;
use App\Models\Trade;
use App\Services\TradeService;
use Illuminate\Http\Request;

class TradeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $trades = Trade::query()
            ->with(['commodity', 'buyer', 'supplier'])
            ->when(! $user->isAdmin(), function ($q) use ($user) {
                $q->where(function ($inner) use ($user) {
                    $inner->where('buyer_id', $user->id)
                        ->orWhere('supplier_id', $user->id)
                        ->orWhere('logistics_user_id', $user->id)
                        ->orWhere('insurer_user_id', $user->id);
                    if ($user->role === 'logistics') {
                        $inner->orWhere(function ($open) {
                            $open->where('delivery_mode', 'platform')->whereNull('logistics_user_id')->where('status', 'contract_issued');
                        });
                    }
                    if ($user->role === 'insurer') {
                        $inner->orWhereIn('status', ['contract_issued', 'logistics_assigned', 'in_transit']);
                    }
                });
            })
            ->latest()
            ->paginate(12);

        return view('trades.index', ['trades' => $trades]);
    }

    public function show(Trade $trade)
    {
        $this->visible($trade);
        $trade->load([
            'commodity', 'buyer', 'supplier', 'haulier', 'insurer', 'supply', 'demand',
            'events.user', 'haulQuotes.partner', 'haulQuotes.truck', 'insuranceQuotes.partner',
            'invoice', 'payments', 'disputes.raiser',
        ]);

        return view('trades.show', ['trade' => $trade]);
    }

    public function statement(Trade $trade)
    {
        $this->visible($trade);
        $trade->load(['commodity', 'buyer', 'supplier', 'haulier', 'insurer', 'events.user', 'invoice']);

        return view('trades.statement', ['trade' => $trade]);
    }

    public function matches(Request $request)
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['buyer', 'aggregator', 'farmer'], true), 403);

        $matches = MatchSuggestion::query()
            ->with(['supply.commodity', 'supply.owner', 'demand.buyer', 'demand.commodity'])
            ->where('status', 'suggested')
            ->where(function ($q) use ($user) {
                $q->whereHas('supply', fn ($s) => $s->where('user_id', $user->id))
                    ->orWhereHas('demand', fn ($d) => $d->where('user_id', $user->id));
            })
            ->orderByDesc('score')
            ->paginate(12);

        return view('matches.index', ['matches' => $matches]);
    }

    public function agree(Request $request, MatchSuggestion $match, TradeService $trades)
    {
        $trade = $trades->agreeMatch($request->user(), $match);

        return redirect()->route('trades.show', $trade)->with('status', $trade->code.' is agreed.');
    }

    public function dismiss(Request $request, MatchSuggestion $match)
    {
        $user = $request->user();
        $match->load(['supply', 'demand']);
        abort_unless($user->id === $match->supply->user_id || $user->id === $match->demand->user_id, 403);
        $match->update(['status' => 'dismissed']);

        return back()->with('status', 'Match dismissed.');
    }

    public function contract(Request $request, Trade $trade, TradeService $trades)
    {
        $trades->issueContract($trade, $request->user());

        return back()->with('status', 'Contract issued.');
    }

    public function acceptHaul(Request $request, Trade $trade, HaulQuote $quote, TradeService $trades)
    {
        $trades->acceptHaul($trade, $quote, $request->user());

        return back()->with('status', 'Haulier assigned.');
    }

    public function acceptCover(Request $request, Trade $trade, InsuranceQuote $quote, TradeService $trades)
    {
        $trades->acceptCover($trade, $quote, $request->user());

        return back()->with('status', 'Cover attached to the trade.');
    }

    public function transition(Request $request, Trade $trade, TradeService $trades)
    {
        $data = $request->validate([
            'status' => ['required', 'in:in_transit,delivered,quality_verified,closed'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);
        $trades->transition($trade, $data['status'], $request->user(), $data['note'] ?? null);

        return back()->with('status', 'Trade moved to '.status_label('trade_statuses', $data['status']).'.');
    }

    public function pay(Request $request, Trade $trade, TradeService $trades)
    {
        $trades->settle($trade, $request->user());

        return back()->with('status', 'Payment settled from the buyer wallet.');
    }

    public function dispute(Request $request, Trade $trade, TradeService $trades)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:2000']]);
        $trades->dispute($trade, $request->user(), $data['reason']);

        return back()->with('status', 'Dispute opened. An operator will review it.');
    }

    private function visible(Trade $trade): void
    {
        $user = request()->user();
        if ($trade->involves($user)) {
            return;
        }
        if ($user->role === 'logistics' && $trade->status === 'contract_issued' && $trade->logistics_user_id === null) {
            return;
        }
        if ($user->role === 'insurer' && in_array($trade->status, ['contract_issued', 'logistics_assigned', 'in_transit', 'delivered'], true)) {
            return;
        }
        abort(403);
    }
}
