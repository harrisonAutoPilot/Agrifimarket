<?php

namespace App\Http\Controllers;

use App\Models\DemandRequest;
use App\Models\MatchSuggestion;
use App\Models\Price;
use App\Models\SupplyListing;
use App\Models\Trade;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.home');
        }

        $trades = Trade::query()
            ->with('commodity')
            ->where(function ($q) use ($user) {
                $q->where('buyer_id', $user->id)
                    ->orWhere('supplier_id', $user->id)
                    ->orWhere('logistics_user_id', $user->id)
                    ->orWhere('insurer_user_id', $user->id);
            })
            ->latest()
            ->limit(6)
            ->get();

        $matches = MatchSuggestion::query()
            ->where('status', 'suggested')
            ->where(function ($q) use ($user) {
                $q->whereHas('supply', fn ($s) => $s->where('user_id', $user->id))
                    ->orWhereHas('demand', fn ($d) => $d->where('user_id', $user->id));
            })
            ->count();

        return view('dashboard.home', [
            'trades' => $trades,
            'prices' => Price::query()->with(['commodity', 'market'])->latest('recorded_on')->limit(5)->get(),
            'matches' => $matches,
            'activeSupplies' => SupplyListing::query()->where('user_id', $user->id)->where('status', 'active')->count(),
            'activeDemands' => DemandRequest::query()->where('status', 'active')->count(),
            'myDemands' => DemandRequest::query()->where('user_id', $user->id)->where('status', 'active')->count(),
        ]);
    }
}
