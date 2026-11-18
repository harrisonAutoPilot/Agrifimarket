<?php

namespace App\Http\Controllers;

use App\Models\Commodity;
use App\Models\DemandRequest;
use App\Services\MatchingService;
use App\Services\TradeService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DemandController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->role === 'buyer', 403);

        return view('demands.index', [
            'orders' => DemandRequest::query()->with('commodity')->where('user_id', $request->user()->id)->latest()->paginate(12),
        ]);
    }

    public function board(Request $request)
    {
        abort_unless(in_array($request->user()->role, ['farmer', 'aggregator'], true), 403);

        $orders = DemandRequest::query()
            ->with(['commodity', 'buyer'])
            ->where('status', 'active')
            ->when($request->filled('state'), fn ($q) => $q->where('state', $request->string('state')))
            ->when($request->filled('commodity'), fn ($q) => $q->where('commodity_id', $request->integer('commodity')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('demands.board', [
            'orders' => $orders,
            'commodities' => Commodity::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        abort_unless(request()->user()->role === 'buyer', 403);

        return view('demands.form', [
            'order' => new DemandRequest([
                'delivery_type' => 'pickup',
                'payment_days' => 7,
                'grade' => 'FAQ',
                'moisture_max' => 13,
                'purity_min' => 98,
                'status' => 'active',
                'starts_on' => now()->toDateString(),
                'ends_on' => now()->addDays(14)->toDateString(),
            ]),
            'commodities' => Commodity::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, MatchingService $matching)
    {
        abort_unless($request->user()->role === 'buyer', 403);
        $order = DemandRequest::create([
            ...$this->validated($request),
            'code' => 'TMP',
            'user_id' => $request->user()->id,
            'quantity_filled' => 0,
        ]);
        $order->update(['code' => 'DEM-'.str_pad((string) $order->id, 5, '0', STR_PAD_LEFT)]);
        $matching->refreshDemand($order);

        return redirect()->route('demands.index')->with('status', $order->code.' is open for suppliers.');
    }

    public function edit(DemandRequest $demand)
    {
        $this->owns(request(), $demand);

        return view('demands.form', [
            'order' => $demand,
            'commodities' => Commodity::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, DemandRequest $demand, MatchingService $matching)
    {
        $this->owns($request, $demand);
        abort_unless(in_array($demand->status, ['draft', 'active', 'paused'], true), 422);
        $demand->update($this->validated($request));
        $matching->refreshDemand($demand->fresh());

        return redirect()->route('demands.index')->with('status', $demand->code.' updated.');
    }

    public function status(Request $request, DemandRequest $demand, MatchingService $matching)
    {
        $this->owns($request, $demand);
        $status = $request->validate(['status' => ['required', Rule::in(['active', 'paused', 'withdrawn'])]])['status'];
        $demand->update(['status' => $status]);
        $matching->refreshDemand($demand->fresh());

        return back()->with('status', $demand->code.' marked '.status_label('listing_statuses', $status).'.');
    }

    public function acceptForm(DemandRequest $demand)
    {
        abort_unless(in_array(request()->user()->role, ['farmer', 'aggregator'], true), 403);
        abort_unless($demand->status === 'active', 404);
        $demand->load(['commodity', 'buyer']);

        return view('demands.accept', ['order' => $demand]);
    }

    public function accept(Request $request, DemandRequest $demand, TradeService $trades)
    {
        abort_unless(in_array($request->user()->role, ['farmer', 'aggregator'], true), 403);
        $data = $request->validate([
            'quantity' => ['required', 'numeric', 'min:0.1'],
            'price_per_kg' => ['required', 'numeric', 'min:1'],
            'moisture' => ['nullable', 'numeric', 'min:0', 'max:40'],
            'purity' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'grade' => ['nullable', Rule::in(config('marketplace.grades'))],
            'delivery_mode' => ['required', Rule::in(['self', 'platform'])],
            'logistics_cost' => ['nullable', 'numeric', 'min:0'],
            'state' => ['required', Rule::in(config('marketplace.states'))],
            'city' => ['required', 'string', 'max:80'],
            'address' => ['nullable', 'string', 'max:200'],
        ]);

        $trade = $trades->acceptDemand($request->user(), $demand, $data);

        return redirect()->route('trades.show', $trade)->with('status', 'Order accepted. '.$trade->code.' is now a trade.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'commodity_id' => ['required', 'exists:commodities,id'],
            'quantity' => ['required', 'numeric', 'min:0.1', 'max:100000'],
            'price_per_kg' => ['required', 'numeric', 'min:1'],
            'moisture_max' => ['nullable', 'numeric', 'min:0', 'max:40'],
            'purity_min' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'grade' => ['nullable', Rule::in(config('marketplace.grades'))],
            'delivery_type' => ['required', Rule::in(array_keys(config('marketplace.delivery_types')))],
            'state' => ['required', Rule::in(config('marketplace.states'))],
            'city' => ['required', 'string', 'max:80'],
            'address' => ['nullable', 'string', 'max:200'],
            'payment_days' => ['required', 'integer', 'min:1', 'max:60'],
            'status' => ['required', Rule::in(['draft', 'active'])],
            'description' => ['nullable', 'string', 'max:2000'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
        ]);
    }

    private function owns(Request $request, DemandRequest $demand): void
    {
        abort_unless($request->user()->role === 'buyer' && $demand->user_id === $request->user()->id, 403);
    }
}
