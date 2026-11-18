<?php

namespace App\Services;

use App\Models\Commodity;
use App\Models\DemandRequest;
use App\Models\SupplyListing;

class CalculatorService
{
    public function estimate(array $input): array
    {
        $quantity = (float) $input['quantity'];
        $price = (float) $input['price_per_kg'];
        $factor = (float) ($input['grade_factor'] ?? 1);
        $factor = $factor > 0 ? $factor : 1;

        $tradeValue = round($quantity * 1000 * $price * $factor, 2);
        $commission = round($tradeValue * (float) config('marketplace.commission_rate'), 2);
        $fees = round((float) ($input['fees'] ?? config('marketplace.platform_fee')), 2);

        $logistics = array_key_exists('logistics_cost', $input) && $input['logistics_cost'] !== null && $input['logistics_cost'] !== ''
            ? round((float) $input['logistics_cost'], 2)
            : round($quantity * (float) config('marketplace.haul_base_per_tonne'), 2);

        $insurance = array_key_exists('insurance_premium', $input) && $input['insurance_premium'] !== null && $input['insurance_premium'] !== ''
            ? round((float) $input['insurance_premium'], 2)
            : round($tradeValue * 0.004, 2);

        return [
            'quantity' => $quantity,
            'price_per_kg' => $price,
            'grade_factor' => $factor,
            'trade_value' => $tradeValue,
            'logistics_cost' => $logistics,
            'insurance_premium' => $insurance,
            'fees' => $fees,
            'commission' => $commission,
            'landed_cost' => round($tradeValue + $logistics + $insurance + $fees, 2),
            'net_proceeds' => round($tradeValue - $commission, 2),
            'commodity' => isset($input['commodity_id']) ? Commodity::find($input['commodity_id']) : null,
        ];
    }
}
