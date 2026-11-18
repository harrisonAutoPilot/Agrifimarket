<?php

namespace App\Http\Controllers;

use App\Models\InsuranceProduct;
use App\Models\InsuranceQuote;
use App\Models\Trade;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InsuranceController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->role === 'insurer', 403);

        $trades = Trade::query()
            ->with(['commodity', 'buyer', 'supplier', 'insuranceQuotes'])
            ->whereIn('status', ['contract_issued', 'logistics_assigned', 'in_transit', 'delivered'])
            ->latest()
            ->limit(20)
            ->get();

        return view('insurance.index', [
            'products' => InsuranceProduct::query()->where('user_id', $request->user()->id)->get(),
            'trades' => $trades,
        ]);
    }

    public function storeProduct(Request $request)
    {
        abort_unless($request->user()->role === 'insurer', 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', Rule::in(['cargo', 'trade_credit', 'warehouse'])],
            'rate_percent' => ['required', 'numeric', 'min:0.01', 'max:20'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        InsuranceProduct::create([...$data, 'user_id' => $request->user()->id, 'active' => true]);

        return back()->with('status', 'Cover product saved.');
    }

    public function quote(Request $request, Trade $trade)
    {
        abort_unless($request->user()->role === 'insurer', 403);
        abort_unless(in_array($trade->status, ['contract_issued', 'logistics_assigned', 'in_transit'], true), 422);

        $data = $request->validate([
            'insurance_product_id' => ['required', 'exists:insurance_products,id'],
            'cover_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $product = InsuranceProduct::query()
            ->whereKey($data['insurance_product_id'])
            ->where('user_id', $request->user()->id)
            ->where('active', true)
            ->firstOrFail();

        $premium = round((float) $trade->commodity_value * ((float) $product->rate_percent / 100), 2);
        if ($premium <= 0) {
            $premium = round((float) $trade->quantity * 1000 * (float) $trade->price_per_kg * ((float) $product->rate_percent / 100), 2);
        }

        InsuranceQuote::updateOrCreate(
            ['trade_id' => $trade->id, 'user_id' => $request->user()->id],
            [
                'insurance_product_id' => $product->id,
                'premium' => $premium,
                'cover_note' => $data['cover_note'] ?? $product->name,
                'status' => 'quoted',
            ]
        );

        return redirect()->route('trades.show', $trade)->with('status', 'Premium quoted at '.naira($premium).'.');
    }
}
