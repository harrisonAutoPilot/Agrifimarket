<?php

namespace App\Http\Controllers;

use App\Models\CollectionCenter;
use App\Models\Commodity;
use App\Models\DealerListing;
use App\Models\Dispute;
use App\Models\Document;
use App\Models\InputApplication;
use App\Models\InputBundle;
use App\Models\Market;
use App\Models\Price;
use App\Models\Trade;
use App\Models\Truck;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\TradeService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function home()
    {
        return view('admin.home', [
            'pendingUsers' => User::query()->whereIn('status', ['pending', 'under_review'])->latest()->get(),
            'pendingDocs' => Document::query()->with('user')->where('status', 'pending')->latest()->limit(12)->get(),
            'pendingListings' => DealerListing::query()->with('dealer')->where('status', 'pending')->latest()->get(),
            'pendingTrucks' => Truck::query()->with('owner')->where('status', 'pending')->latest()->get(),
            'openDisputes' => Dispute::query()->with(['trade', 'raiser'])->where('status', 'open')->latest()->get(),
            'creditQueue' => InputApplication::query()->with(['farmer', 'bundle'])->where('status', 'submitted')->latest()->get(),
        ]);
    }

    public function users(Request $request)
    {
        $users = User::query()
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->string('role')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users', ['users' => $users]);
    }

    public function showUser(User $user)
    {
        $user->load(['documents', 'wallet']);

        return view('admin.user', ['member' => $user]);
    }

    public function updateUser(Request $request, User $user, NotificationService $notifications)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(config('marketplace.user_statuses')))],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $user->update([
            'status' => $data['status'],
            'rejection_reason' => $data['status'] === 'rejected' ? ($data['rejection_reason'] ?? 'Documents need another look.') : null,
            'approved_at' => $data['status'] === 'approved' ? ($user->approved_at ?? now()) : $user->approved_at,
        ]);

        $notifications->send(
            $user,
            'Desk status: '.status_label('user_statuses', $data['status']),
            $data['status'] === 'approved'
                ? 'Your AgrifiMarket desk is open.'
                : ($user->rejection_reason ?: 'An operator updated your desk.'),
            route('onboarding')
        );

        return back()->with('status', $user->deskName().' is now '.status_label('user_statuses', $data['status']).'.');
    }

    public function creditUser(Request $request, User $user, WalletService $wallets)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'narration' => ['required', 'string', 'max:160'],
        ]);

        $wallets->credit($user, (float) $data['amount'], $data['narration'], 'FUND-'.now()->format('YmdHis').'-'.$user->id);

        return back()->with('status', naira($data['amount']).' credited to '.$user->deskName().'.');
    }

    public function reviewDocument(Request $request, Document $document)
    {
        $document->update([
            'status' => $request->validate(['status' => ['required', Rule::in(['approved', 'rejected'])]])['status'],
        ]);

        return back()->with('status', 'Document marked '.$document->status.'.');
    }

    public function reviewListing(Request $request, DealerListing $listing)
    {
        $listing->update([
            'status' => $request->validate(['status' => ['required', Rule::in(['active', 'rejected'])]])['status'],
        ]);

        return back()->with('status', $listing->name.' marked '.$listing->status.'.');
    }

    public function reviewTruck(Request $request, Truck $truck)
    {
        $truck->update([
            'status' => $request->validate(['status' => ['required', Rule::in(['verified', 'rejected'])]])['status'],
        ]);

        return back()->with('status', $truck->registration_number.' marked '.$truck->status.'.');
    }

    public function approveCredit(InputApplication $application, NotificationService $notifications)
    {
        abort_unless($application->status === 'submitted', 422);
        $application->update(['status' => 'contribution_due']);
        $notifications->send(
            $application->farmer,
            'Input credit approved',
            'Pay your contribution of '.naira($application->contribution_amount).' to release '.$application->bundle->name.'.',
            route('credit.show', $application)
        );

        return back()->with('status', 'Farmer can now pay the contribution.');
    }

    public function prices()
    {
        return view('admin.prices', [
            'commodities' => Commodity::orderBy('name')->get(),
            'markets' => Market::orderBy('name')->get(),
            'prices' => Price::query()->with(['commodity', 'market'])->latest('recorded_on')->limit(20)->get(),
        ]);
    }

    public function storePrice(Request $request)
    {
        $data = $request->validate([
            'commodity_id' => ['required', 'exists:commodities,id'],
            'market_id' => ['required', 'exists:markets,id'],
            'price_per_kg' => ['required', 'numeric', 'min:1'],
            'bag_weight_kg' => ['required', 'numeric', 'min:1', 'max:200'],
            'recorded_on' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $perKg = round((float) $data['price_per_kg'], 2);
        $bag = round($perKg * (float) $data['bag_weight_kg'], 2);

        Price::create([
            'commodity_id' => $data['commodity_id'],
            'market_id' => $data['market_id'],
            'price_per_kg' => $perKg,
            'price_per_bag' => $bag,
            'price_per_tonne' => round($perKg * 1000, 2),
            'bag_weight_kg' => $data['bag_weight_kg'],
            'recorded_on' => $data['recorded_on'],
            'source' => 'official',
            'notes' => $data['notes'] ?? null,
            'published_by' => $request->user()->id,
        ]);

        return back()->with('status', 'Official price published.');
    }

    public function trades()
    {
        return view('admin.trades', [
            'trades' => Trade::query()->with(['commodity', 'buyer', 'supplier'])->latest()->paginate(20),
        ]);
    }

    public function disputes()
    {
        return view('admin.disputes', [
            'disputes' => Dispute::query()->with(['trade.commodity', 'raiser'])->latest()->paginate(20),
        ]);
    }

    public function resolveDispute(Request $request, Dispute $dispute, TradeService $trades)
    {
        $data = $request->validate([
            'outcome' => ['required', Rule::in(['restore', 'close'])],
            'resolution' => ['required', 'string', 'max:2000'],
        ]);
        $trades->resolve($dispute, $request->user(), $data['outcome'], $data['resolution']);

        return back()->with('status', 'Dispute resolved.');
    }

    public function centers()
    {
        return view('admin.centers', [
            'centers' => CollectionCenter::query()->orderBy('state')->get(),
        ]);
    }

    public function storeCenter(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:140'],
            'state' => ['required', Rule::in(config('marketplace.states'))],
            'city' => ['required', 'string', 'max:80'],
            'address' => ['required', 'string', 'max:200'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);
        CollectionCenter::create([...$data, 'active' => true]);

        return back()->with('status', 'Hub added.');
    }

    public function bundles()
    {
        return view('admin.bundles', ['bundles' => InputBundle::query()->latest()->get()]);
    }

    public function storeBundle(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:140'],
            'season' => ['required', 'string', 'max:40'],
            'coverage_ha' => ['required', 'numeric', 'min:0.1'],
            'price' => ['required', 'numeric', 'min:1'],
            'contribution_percent' => ['required', 'numeric', 'min:1', 'max:90'],
            'description' => ['nullable', 'string', 'max:1000'],
            'items' => ['nullable', 'string', 'max:1000'],
        ]);

        InputBundle::create([
            ...collect($data)->except('items')->all(),
            'items' => collect(preg_split('/\r\n|\r|\n/', (string) ($data['items'] ?? '')))->filter()->values()->all(),
            'active' => true,
        ]);

        return back()->with('status', 'Bundle published.');
    }

    public function reports()
    {
        $byStatus = Trade::query()->selectRaw('status, COUNT(*) as total, SUM(commodity_value) as value')->groupBy('status')->get();

        return view('admin.reports', [
            'byStatus' => $byStatus,
            'members' => User::query()->selectRaw('role, COUNT(*) as total')->groupBy('role')->get(),
            'settled' => (float) Trade::query()->whereIn('status', ['payment_settled', 'closed'])->sum('commodity_value'),
        ]);
    }

    public function exportTrades(): StreamedResponse
    {
        $trades = Trade::query()->with(['commodity', 'buyer', 'supplier'])->orderBy('id')->get();

        return response()->streamDownload(function () use ($trades) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Code', 'Status', 'Commodity', 'Tonnes', 'Price/kg', 'Buyer', 'Supplier', 'Value', 'Buyer total', 'Supplier net', 'Agreed']);
            foreach ($trades as $trade) {
                fputcsv($out, [
                    $trade->code,
                    $trade->status,
                    $trade->commodity->name,
                    $trade->quantity,
                    $trade->price_per_kg,
                    $trade->buyer->deskName(),
                    $trade->supplier->deskName(),
                    $trade->commodity_value,
                    $trade->buyer_total,
                    $trade->supplier_net,
                    optional($trade->agreed_at)->toDateTimeString(),
                ]);
            }
            fclose($out);
        }, 'agrifimarket-trades.csv', ['Content-Type' => 'text/csv']);
    }
}
