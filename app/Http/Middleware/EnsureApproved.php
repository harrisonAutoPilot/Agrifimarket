<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->status === 'suspended') {
            abort(403, 'This AgrifiMarket desk is suspended. Write to '.config('marketplace.contact_email').'.');
        }

        if ($user && ! $user->isApproved()) {
            return redirect()
                ->route('onboarding')
                ->with('status', 'Your desk opens after an operator approves your documents.');
        }

        return $next($request);
    }
}
