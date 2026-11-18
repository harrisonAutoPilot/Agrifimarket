<?php

namespace App\Providers;

use App\Models\Price;
use App\Support\Desk;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::defaultView('partials.pagination');
        Paginator::defaultSimpleView('partials.pagination');

        View::composer(['layouts.public', 'layouts.app'], function ($view) {
            $view->with(
                'ticker',
                Price::query()->with(['commodity', 'market'])->latest('recorded_on')->latest('id')->limit(14)->get()
            );
        });

        View::composer('layouts.app', function ($view) {
            $user = auth()->user();
            $view->with('navItems', $user ? Desk::nav($user) : []);
            $view->with('unreadAlerts', $user ? $user->alerts()->whereNull('read_at')->count() : 0);
        });
    }
}
