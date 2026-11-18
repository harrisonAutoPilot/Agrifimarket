<?php

namespace App\Http\Controllers;

use App\Models\CalculatorSnapshot;
use App\Models\Commodity;
use App\Models\Trade;
use App\Services\CalculatorService;
use Illuminate\Http\Request;

class CalculatorController extends Controller
{
    public function index(Request $request, CalculatorService $calculator)
    {
        $result = null;
        if ($request->isMethod('post') || $request->filled('quantity')) {
            $data = $request->validate([
                'commodity_id' => ['nullable', 'exists:commodities,id'],
                'quantity' => ['required', 'numeric', 'min:0.1'],
                'price_per_kg' => ['required', 'numeric', 'min:1'],
                'grade_factor' => ['nullable', 'numeric', 'min:0.5', 'max:1.5'],
                'logistics_cost' => ['nullable', 'numeric', 'min:0'],
                'insurance_premium' => ['nullable', 'numeric', 'min:0'],
                'trade_id' => ['nullable', 'exists:trades,id'],
                'note' => ['nullable', 'string', 'max:500'],
            ]);
            $result = $calculator->estimate($data);

            if ($request->boolean('save')) {
                $snapshot = CalculatorSnapshot::create([
                    'user_id' => $request->user()->id,
                    'trade_id' => $data['trade_id'] ?? null,
                    'commodity_id' => $data['commodity_id'] ?? null,
                    'quantity' => $result['quantity'],
                    'price_per_kg' => $result['price_per_kg'],
                    'grade_factor' => $result['grade_factor'],
                    'logistics_cost' => $result['logistics_cost'],
                    'insurance_premium' => $result['insurance_premium'],
                    'fees' => $result['fees'],
                    'commission' => $result['commission'],
                    'trade_value' => $result['trade_value'],
                    'landed_cost' => $result['landed_cost'],
                    'net_proceeds' => $result['net_proceeds'],
                    'note' => $data['note'] ?? null,
                ]);
                $result['saved'] = $snapshot->id;
            }
        }

        return view('calculator.index', [
            'result' => $result,
            'commodities' => Commodity::orderBy('name')->get(),
            'trades' => Trade::query()->where(function ($q) use ($request) {
                $id = $request->user()->id;
                $q->where('buyer_id', $id)->orWhere('supplier_id', $id);
            })->latest()->limit(20)->get(),
            'snapshots' => CalculatorSnapshot::query()->with('commodity')->where('user_id', $request->user()->id)->latest()->limit(8)->get(),
        ]);
    }
}
