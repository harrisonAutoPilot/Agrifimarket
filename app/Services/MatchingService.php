<?php

namespace App\Services;

use App\Models\DemandRequest;
use App\Models\MatchSuggestion;
use App\Models\SupplyListing;

class MatchingService
{
    public function refreshSupply(SupplyListing $supply): void
    {
        if ($supply->status !== 'active') {
            MatchSuggestion::query()
                ->where('supply_listing_id', $supply->id)
                ->where('status', 'suggested')
                ->delete();

            return;
        }

        $demands = DemandRequest::query()
            ->where('commodity_id', $supply->commodity_id)
            ->where('status', 'active')
            ->get();

        $keep = [];
        foreach ($demands as $demand) {
            $existing = MatchSuggestion::query()
                ->where('supply_listing_id', $supply->id)
                ->where('demand_request_id', $demand->id)
                ->first();
            if ($existing && $existing->status !== 'suggested') {
                continue;
            }

            $scored = $this->score($supply, $demand);
            if ($scored['score'] < 50 || $demand->remaining() <= 0) {
                continue;
            }
            $keep[] = $demand->id;
            MatchSuggestion::query()->updateOrCreate(
                ['supply_listing_id' => $supply->id, 'demand_request_id' => $demand->id],
                ['score' => $scored['score'], 'reasons' => $scored['reasons'], 'status' => 'suggested']
            );
        }

        MatchSuggestion::query()
            ->where('supply_listing_id', $supply->id)
            ->where('status', 'suggested')
            ->when($keep, fn ($q) => $q->whereNotIn('demand_request_id', $keep), fn ($q) => $q)
            ->delete();
    }

    public function refreshDemand(DemandRequest $demand): void
    {
        if ($demand->status !== 'active') {
            MatchSuggestion::query()
                ->where('demand_request_id', $demand->id)
                ->where('status', 'suggested')
                ->delete();

            return;
        }

        $supplies = SupplyListing::query()
            ->where('commodity_id', $demand->commodity_id)
            ->where('status', 'active')
            ->get();

        $keep = [];
        foreach ($supplies as $supply) {
            $existing = MatchSuggestion::query()
                ->where('supply_listing_id', $supply->id)
                ->where('demand_request_id', $demand->id)
                ->first();
            if ($existing && $existing->status !== 'suggested') {
                continue;
            }

            $scored = $this->score($supply, $demand);
            if ($scored['score'] < 50) {
                continue;
            }
            $keep[] = $supply->id;
            MatchSuggestion::query()->updateOrCreate(
                ['supply_listing_id' => $supply->id, 'demand_request_id' => $demand->id],
                ['score' => $scored['score'], 'reasons' => $scored['reasons'], 'status' => 'suggested']
            );
        }

        MatchSuggestion::query()
            ->where('demand_request_id', $demand->id)
            ->where('status', 'suggested')
            ->when($keep, fn ($q) => $q->whereNotIn('supply_listing_id', $keep))
            ->delete();
    }

    public function score(SupplyListing $supply, DemandRequest $demand): array
    {
        $reasons = ['Same commodity'];
        $score = 40;

        $bid = (float) $demand->price_per_kg;
        $ask = (float) $supply->price_per_kg;

        if ($bid >= $ask) {
            $score += 25;
            $reasons[] = 'Buyer bid covers the offer';
        } else {
            $gap = ($ask - $bid) / max($bid, 1);
            if ($gap <= 0.05) {
                $score += 15;
                $reasons[] = 'Prices are within 5%';
            } elseif ($gap <= 0.1) {
                $score += 8;
                $reasons[] = 'Prices are within 10%';
            }
        }

        if ($supply->state === $demand->state) {
            $score += 20;
            $reasons[] = 'Same state';
        }

        if ($supply->moisture !== null && $demand->moisture_max !== null && (float) $supply->moisture <= (float) $demand->moisture_max) {
            $score += 8;
            $reasons[] = 'Moisture is inside the buyer limit';
        }

        if ($supply->purity !== null && $demand->purity_min !== null && (float) $supply->purity >= (float) $demand->purity_min) {
            $score += 7;
            $reasons[] = 'Purity meets the buyer floor';
        }

        return ['score' => min(100, $score), 'reasons' => $reasons];
    }
}
