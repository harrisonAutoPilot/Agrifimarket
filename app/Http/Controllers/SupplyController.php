<?php

namespace App\Http\Controllers;

use App\Models\Commodity;
use App\Models\SupplyListing;
use App\Models\VaultFile;
use App\Services\MatchingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplyController extends Controller
{
    public function index(Request $request)
    {
        $this->seller($request);

        $listings = SupplyListing::query()
            ->with('commodity')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(12);

        return view('supplies.index', ['listings' => $listings]);
    }

    public function create()
    {
        $this->seller(request());

        return view('supplies.form', [
            'listing' => new SupplyListing(['status' => 'active', 'grade' => 'FAQ', 'moisture' => 12, 'purity' => 98]),
            'commodities' => Commodity::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, MatchingService $matching)
    {
        $this->seller($request);
        $listing = SupplyListing::create([
            ...$this->validated($request),
            'code' => 'TMP',
            'user_id' => $request->user()->id,
        ]);
        $listing->update(['code' => 'SUP-'.str_pad((string) $listing->id, 5, '0', STR_PAD_LEFT)]);
        $this->storeFiles($request, $listing);
        $matching->refreshSupply($listing);

        return redirect()->route('supplies.index')->with('status', $listing->code.' is on the board.');
    }

    public function edit(SupplyListing $supply)
    {
        $this->owns(request(), $supply);

        return view('supplies.form', [
            'listing' => $supply,
            'commodities' => Commodity::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, SupplyListing $supply, MatchingService $matching)
    {
        $this->owns($request, $supply);
        abort_unless(in_array($supply->status, ['draft', 'active', 'paused'], true), 422);
        $supply->update($this->validated($request));
        $this->storeFiles($request, $supply);
        $matching->refreshSupply($supply->fresh());

        return redirect()->route('supplies.index')->with('status', $supply->code.' updated.');
    }

    public function status(Request $request, SupplyListing $supply, MatchingService $matching)
    {
        $this->owns($request, $supply);
        $status = $request->validate(['status' => ['required', Rule::in(['active', 'paused', 'withdrawn'])]])['status'];
        $supply->update(['status' => $status]);
        $matching->refreshSupply($supply->fresh());

        return back()->with('status', $supply->code.' marked '.status_label('listing_statuses', $status).'.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'commodity_id' => ['required', 'exists:commodities,id'],
            'quantity' => ['required', 'numeric', 'min:0.1', 'max:100000'],
            'price_per_kg' => ['required', 'numeric', 'min:1'],
            'moisture' => ['nullable', 'numeric', 'min:0', 'max:40'],
            'purity' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'grade' => ['nullable', Rule::in(config('marketplace.grades'))],
            'state' => ['required', Rule::in(config('marketplace.states'))],
            'city' => ['required', 'string', 'max:80'],
            'address' => ['nullable', 'string', 'max:200'],
            'status' => ['required', Rule::in(['draft', 'active'])],
            'description' => ['nullable', 'string', 'max:2000'],
            'expires_on' => ['nullable', 'date', 'after:today'],
            'files.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);
    }

    private function storeFiles(Request $request, SupplyListing $listing): void
    {
        foreach ($request->file('files', []) as $file) {
            VaultFile::create([
                'fileable_type' => $listing->getMorphClass(),
                'fileable_id' => $listing->id,
                'type' => 'attachment',
                'original_name' => $file->getClientOriginalName(),
                'path' => $file->store('listings/'.$listing->id, 'public'),
            ]);
        }
    }

    private function seller(Request $request): void
    {
        abort_unless(in_array($request->user()->role, ['farmer', 'aggregator'], true), 403);
    }

    private function owns(Request $request, SupplyListing $supply): void
    {
        $this->seller($request);
        abort_unless($supply->user_id === $request->user()->id, 403);
    }
}
