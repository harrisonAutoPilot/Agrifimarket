<?php

namespace App\Support;

use App\Models\User;

class Desk
{
    public static function nav(User $user): array
    {
        $items = [
            ['Overview', 'dashboard', ['farmer', 'aggregator', 'buyer', 'dealer', 'logistics', 'insurer']],
            ['My supply', 'supplies.index', ['farmer', 'aggregator']],
            ['Orders to fill', 'demands.board', ['farmer', 'aggregator']],
            ['My buy orders', 'demands.index', ['buyer']],
            ['Trades', 'trades.index', ['farmer', 'aggregator', 'buyer', 'logistics', 'insurer']],
            ['Matches', 'matches.index', ['farmer', 'aggregator', 'buyer']],
            ['Fleet', 'logistics.fleet', ['logistics']],
            ['Haul board', 'logistics.jobs', ['logistics']],
            ['Cover desk', 'insurance.index', ['insurer']],
            ['Input shop', 'dealer.index', ['dealer']],
            ['Input credit', 'credit.index', ['farmer']],
            ['Market prices', 'prices.index', ['farmer', 'aggregator', 'buyer', 'dealer', 'logistics', 'insurer', 'admin']],
            ['Calculator', 'calculator.index', ['farmer', 'aggregator', 'buyer', 'dealer', 'logistics', 'insurer', 'admin']],
            ['Wallet', 'wallet.index', ['farmer', 'aggregator', 'buyer', 'dealer', 'logistics', 'insurer', 'admin']],
            ['Alerts', 'notifications.index', ['farmer', 'aggregator', 'buyer', 'dealer', 'logistics', 'insurer', 'admin']],
            ['Profile', 'profile.edit', ['farmer', 'aggregator', 'buyer', 'dealer', 'logistics', 'insurer', 'admin']],
        ];

        if ($user->isAdmin()) {
            return [
                ['Queue', 'admin.home', []],
                ['Members', 'admin.users', []],
                ['Official prices', 'admin.prices', []],
                ['Trades', 'admin.trades', []],
                ['Disputes', 'admin.disputes', []],
                ['Hubs', 'admin.centers', []],
                ['Input bundles', 'admin.bundles', []],
                ['Reports', 'admin.reports', []],
                ['Calculator', 'calculator.index', []],
                ['Wallet', 'wallet.index', []],
                ['Alerts', 'notifications.index', []],
            ];
        }

        return array_values(array_filter(
            $items,
            fn (array $item) => in_array($user->role, $item[2], true)
        ));
    }
}
