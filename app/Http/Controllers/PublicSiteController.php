<?php

namespace App\Http\Controllers;

use App\Models\CollectionCenter;
use App\Models\Commodity;
use App\Models\DealerListing;
use App\Models\DemandRequest;
use App\Models\Inquiry;
use App\Models\Market;
use App\Models\Price;
use App\Models\SupportMessage;
use App\Models\Trade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicSiteController extends Controller
{
    public function home()
    {
        $orders = DemandRequest::query()
            ->with(['commodity', 'buyer'])
            ->where('status', 'active')
            ->latest()
            ->limit(6)
            ->get();

        $centers = CollectionCenter::query()->where('active', true)->orderBy('state')->get();

        return view('public.home', [
            'orders' => $orders,
            'centers' => $centers,
            'commodityCount' => Commodity::count(),
            'marketCount' => Market::count(),
            'settled' => (float) Trade::query()->whereIn('status', ['payment_settled', 'closed'])->sum('commodity_value'),
            'tradeCount' => Trade::query()->whereIn('status', ['payment_settled', 'closed'])->count(),
        ]);
    }

    public function prices(Request $request)
    {
        $unit = in_array($request->query('unit'), ['kg', 'bag', 'tonne'], true) ? $request->query('unit') : 'kg';

        $prices = Price::query()
            ->with(['commodity', 'market'])
            ->when($request->filled('commodity'), fn ($q) => $q->where('commodity_id', $request->integer('commodity')))
            ->when($request->filled('market'), fn ($q) => $q->where('market_id', $request->integer('market')))
            ->when($request->filled('state'), fn ($q) => $q->whereHas('market', fn ($m) => $m->where('state', $request->string('state'))))
            ->when($request->filled('month'), function ($q) use ($request) {
                $month = $request->date('month');
                if ($month) {
                    $q->whereYear('recorded_on', $month->year)->whereMonth('recorded_on', $month->month);
                }
            })
            ->latest('recorded_on')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $summary = Price::query()
            ->join('markets', 'markets.id', '=', 'prices.market_id')
            ->join('commodities', 'commodities.id', '=', 'prices.commodity_id')
            ->when($request->filled('month'), function ($q) use ($request) {
                $month = $request->date('month');
                if ($month) {
                    $q->whereYear('prices.recorded_on', $month->year)->whereMonth('prices.recorded_on', $month->month);
                }
            }, fn ($q) => $q->where('prices.recorded_on', '>=', now()->startOfMonth()->toDateString()))
            ->groupBy('commodities.id', 'commodities.name')
            ->orderBy('commodities.name')
            ->select([
                'commodities.name',
                DB::raw('MIN(prices.price_per_kg) as min_price'),
                DB::raw('MAX(prices.price_per_kg) as max_price'),
                DB::raw('AVG(prices.price_per_kg) as avg_price'),
            ])
            ->get();

        $focus = null;
        if ($request->filled('commodity')) {
            $focus = Commodity::find($request->integer('commodity'));
        }

        return view('public.prices', [
            'prices' => $prices,
            'summary' => $summary,
            'unit' => $unit,
            'focus' => $focus,
            'commodities' => Commodity::orderBy('name')->get(),
            'markets' => Market::orderBy('state')->orderBy('name')->get(),
        ]);
    }

    public function exportPrices(Request $request): StreamedResponse
    {
        $rows = Price::query()->with(['commodity', 'market'])->orderByDesc('recorded_on')->limit(2000)->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Commodity', 'Market', 'State', 'Price per kg', 'Price per bag', 'Price per tonne', 'Bag kg', 'Source']);
            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->recorded_on->toDateString(),
                    $row->commodity->name,
                    $row->market->name,
                    $row->market->state,
                    $row->price_per_kg,
                    $row->price_per_bag,
                    $row->price_per_tonne,
                    $row->bag_weight_kg,
                    $row->source,
                ]);
            }
            fclose($out);
        }, 'agrifimarket-prices.csv', ['Content-Type' => 'text/csv']);
    }

    public function orders(Request $request)
    {
        $orders = DemandRequest::query()
            ->with(['commodity', 'buyer'])
            ->where('status', 'active')
            ->when($request->filled('commodity'), fn ($q) => $q->where('commodity_id', $request->integer('commodity')))
            ->when($request->filled('state'), fn ($q) => $q->where('state', $request->string('state')))
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('public.orders', [
            'orders' => $orders,
            'commodities' => Commodity::orderBy('name')->get(),
        ]);
    }

    public function marketplace(Request $request)
    {
        $listings = DealerListing::query()
            ->with('dealer')
            ->where('status', 'active')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(fn ($inner) => $inner->where('name', 'like', $term)->orWhere('brand', 'like', $term)->orWhere('city', 'like', $term));
            })
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->filled('state'), fn ($q) => $q->where('state', $request->string('state')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('public.marketplace', ['listings' => $listings]);
    }

    public function listing(DealerListing $listing)
    {
        abort_unless($listing->status === 'active', 404);
        $listing->load('dealer');

        return view('public.listing', ['listing' => $listing]);
    }

    public function inquire(Request $request, DealerListing $listing)
    {
        abort_unless($listing->status === 'active', 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        Inquiry::create([
            ...$data,
            'dealer_listing_id' => $listing->id,
            'from_user_id' => $request->user()?->id,
        ]);

        return back()->with('status', 'Your note is with the dealer. They will use the phone or email you left.');
    }

    public function hubs()
    {
        return view('public.hubs', [
            'centers' => CollectionCenter::query()->where('active', true)->orderBy('state')->get(),
        ]);
    }

    public function support()
    {
        return view('public.support');
    }

    public function storeSupport(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        SupportMessage::create([...$data, 'user_id' => $request->user()?->id]);

        return back()->with('status', 'Message received. The desk replies by email.');
    }

    public function legal(string $page)
    {
        abort_unless(in_array($page, ['terms', 'privacy'], true), 404);

        return view('public.legal', ['page' => $page]);
    }
}
