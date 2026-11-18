<?php

namespace App\Http\Controllers;

use App\Models\InputApplication;
use App\Models\InputBundle;
use App\Services\NotificationService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CreditController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->role === 'farmer', 403);

        return view('credit.index', [
            'bundles' => InputBundle::query()->where('active', true)->get(),
            'applications' => InputApplication::query()->with('bundle')->where('user_id', $request->user()->id)->latest()->get(),
        ]);
    }

    public function apply(Request $request, InputBundle $bundle, NotificationService $notifications)
    {
        abort_unless($request->user()->role === 'farmer', 403);
        abort_unless($bundle->active, 404);

        $exists = InputApplication::query()
            ->where('user_id', $request->user()->id)
            ->where('input_bundle_id', $bundle->id)
            ->whereNotIn('status', ['rejected', 'completed'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages(['bundle' => 'You already have an open application for this bundle.']);
        }

        $contribution = $bundle->contributionAmount();
        $application = InputApplication::create([
            'user_id' => $request->user()->id,
            'input_bundle_id' => $bundle->id,
            'status' => 'submitted',
            'contribution_amount' => $contribution,
            'financed_amount' => round((float) $bundle->price - $contribution, 2),
            'outstanding' => round((float) $bundle->price - $contribution, 2),
        ]);

        $admin = \App\Models\User::query()->where('role', 'admin')->first();
        if ($admin) {
            $notifications->send($admin, 'Input credit application', $request->user()->deskName().' applied for '.$bundle->name.'.', route('admin.home'));
        }

        return redirect()->route('credit.show', $application)->with('status', 'Application submitted.');
    }

    public function show(InputApplication $application)
    {
        abort_unless(request()->user()->id === $application->user_id || request()->user()->isAdmin(), 403);
        $application->load('bundle');

        return view('credit.show', ['application' => $application]);
    }

    public function contribute(Request $request, InputApplication $application, WalletService $wallets)
    {
        abort_unless($request->user()->id === $application->user_id, 403);
        abort_unless($application->status === 'contribution_due', 422);

        $wallets->debit(
            $request->user(),
            (float) $application->contribution_amount,
            'Equity for '.$application->bundle->name,
            'CRD-'.$application->id,
            $application
        );
        $application->update(['status' => 'disbursed']);

        return back()->with('status', 'Contribution received. Inputs can be collected.');
    }

    public function pickup(Request $request, InputApplication $application)
    {
        abort_unless($request->user()->id === $application->user_id, 403);
        abort_unless($application->status === 'disbursed', 422);
        $application->update(['status' => 'repaying']);

        return back()->with('status', 'Pickup confirmed. Repayment is now open.');
    }

    public function repay(Request $request, InputApplication $application, WalletService $wallets)
    {
        abort_unless($request->user()->id === $application->user_id, 403);
        abort_unless($application->status === 'repaying', 422);
        $data = $request->validate(['amount' => ['required', 'numeric', 'min:1']]);
        $amount = min((float) $data['amount'], (float) $application->outstanding);

        $wallets->debit($request->user(), $amount, 'Repayment for '.$application->bundle->name, 'RPY-'.$application->id, $application);
        $left = round((float) $application->outstanding - $amount, 2);
        $application->update([
            'outstanding' => $left,
            'status' => $left <= 0 ? 'completed' : 'repaying',
        ]);

        return back()->with('status', $left <= 0 ? 'Bundle repaid in full.' : 'Repayment recorded. Outstanding '.naira($left).'.');
    }
}
