<?php

use App\Models\User;

function naira(mixed $amount, int $decimals = 2): string
{
    return '₦'.number_format((float) $amount, $decimals);
}

function tonnes(mixed $amount): string
{
    return number_format((float) $amount, 2).' t';
}

function status_label(string $group, ?string $status): string
{
    if ($status === null || $status === '') {
        return '—';
    }

    return config("marketplace.{$group}.{$status}")
        ?? str($status)->replace('_', ' ')->title()->toString();
}

function role_label(?string $role): string
{
    return config("marketplace.roles.{$role}") ?? 'Member';
}

function desk_name(?User $user): string
{
    if (! $user) {
        return 'AgrifiMarket';
    }

    return $user->business_name ?: $user->name;
}
