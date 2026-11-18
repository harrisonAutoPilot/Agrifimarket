<?php

namespace App\Http\Controllers;

use App\Models\DealerListing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DealerController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->role === 'dealer', 403);

        return view('dealer.index', [
            'listings' => DealerListing::query()->withCount('inquiries')->where('user_id', $request->user()->id)->latest()->get(),
            'inquiries' => \App\Models\Inquiry::query()->with('listing')->whereHas('listing', fn ($q) => $q->where('user_id', $request->user()->id))->latest()->limit(20)->get(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->role === 'dealer', 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:140'],
            'category' => ['required', Rule::in(['seed', 'fertilizer', 'pesticide', 'equipment'])],
            'brand' => ['nullable', 'string', 'max:80'],
            'price' => ['required', 'numeric', 'min:1'],
            'unit' => ['required', 'string', 'max:40'],
            'state' => ['required', Rule::in(config('marketplace.states'))],
            'city' => ['required', 'string', 'max:80'],
            'stock' => ['required', 'numeric', 'min:0'],
            'specifications' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        DealerListing::create([...$data, 'user_id' => $request->user()->id, 'status' => 'pending']);

        return back()->with('status', 'Listing submitted. It appears on the shop after approval.');
    }
}
