<?php

namespace App\Http\Controllers;

use App\Models\HaulQuote;
use App\Models\RateCard;
use App\Models\Trade;
use App\Models\Truck;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LogisticsController extends Controller
{
    public function fleet(Request $request)
    {
        $this->haulier($request);

        return view('logistics.fleet', [
            'trucks' => Truck::query()->where('user_id', $request->user()->id)->latest()->get(),
            'rates' => RateCard::query()->where('user_id', $request->user()->id)->get(),
        ]);
    }

    public function storeTruck(Request $request)
    {
        $this->haulier($request);
        $data = $request->validate([
            'registration_number' => ['required', 'string', 'max:20'],
            'driver_name' => ['required', 'string', 'max:120'],
            'driver_phone' => ['required', 'regex:/^0\d{10}$/'],
            'capacity_tonnes' => ['required', 'numeric', 'min:1', 'max:80'],
            'route_zone' => ['required', Rule::in(config('marketplace.zones'))],
        ]);

        Truck::create([...$data, 'user_id' => $request->user()->id, 'status' => 'pending', 'availability' => 'available']);

        return back()->with('status', 'Truck submitted for verification.');
    }

    public function availability(Request $request, Truck $truck)
    {
        $this->haulier($request);
        abort_unless($truck->user_id === $request->user()->id, 403);
        $truck->update([
            'availability' => $request->validate(['availability' => ['required', Rule::in(['available', 'unavailable'])]])['availability'],
        ]);

        return back()->with('status', $truck->registration_number.' updated.');
    }

    public function storeRate(Request $request)
    {
        $this->haulier($request);
        $data = $request->validate([
            'origin_state' => ['required', Rule::in(config('marketplace.states'))],
            'destination_state' => ['required', Rule::in(config('marketplace.states'))],
            'rate_per_tonne' => ['required', 'numeric', 'min:100'],
        ]);
        RateCard::updateOrCreate(
            ['user_id' => $request->user()->id, 'origin_state' => $data['origin_state'], 'destination_state' => $data['destination_state']],
            ['rate_per_tonne' => $data['rate_per_tonne']]
        );

        return back()->with('status', 'Rate card saved.');
    }

    public function jobs(Request $request)
    {
        $this->haulier($request);
        $open = Trade::query()
            ->with(['commodity', 'buyer', 'supplier'])
            ->where('delivery_mode', 'platform')
            ->where('status', 'contract_issued')
            ->whereNull('logistics_user_id')
            ->latest()
            ->get();
        $mine = Trade::query()
            ->with('commodity')
            ->where('logistics_user_id', $request->user()->id)
            ->latest()
            ->limit(12)
            ->get();

        return view('logistics.jobs', [
            'open' => $open,
            'mine' => $mine,
            'trucks' => Truck::query()->where('user_id', $request->user()->id)->where('status', 'verified')->get(),
        ]);
    }

    public function quote(Request $request, Trade $trade)
    {
        $this->haulier($request);
        abort_unless($trade->status === 'contract_issued' && $trade->logistics_user_id === null && $trade->delivery_mode === 'platform', 422);

        $data = $request->validate([
            'truck_id' => ['required', 'exists:trucks,id'],
            'price_per_tonne' => ['required', 'numeric', 'min:100'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $truck = Truck::query()->whereKey($data['truck_id'])->where('user_id', $request->user()->id)->where('status', 'verified')->first();
        abort_unless($truck, 422, 'Choose one of your verified trucks.');

        $total = round((float) $data['price_per_tonne'] * (float) $trade->quantity, 2);
        HaulQuote::updateOrCreate(
            ['trade_id' => $trade->id, 'user_id' => $request->user()->id],
            [
                'truck_id' => $truck->id,
                'price_per_tonne' => $data['price_per_tonne'],
                'total' => $total,
                'note' => $data['note'] ?? null,
                'status' => 'submitted',
            ]
        );

        return redirect()->route('trades.show', $trade)->with('status', 'Quote sent at '.naira($total).'.');
    }

    private function haulier(Request $request): void
    {
        abort_unless($request->user()->role === 'logistics', 403);
    }
}
