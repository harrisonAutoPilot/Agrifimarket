<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function send(User $user, string $title, string $body, ?string $url = null): AppNotification
    {
        $alert = $user->alerts()->create([
            'title' => $title,
            'body' => $body,
            'url' => $url,
        ]);

        try {
            Mail::raw($body.($url ? "\n\n".$url : ''), function ($message) use ($user, $title) {
                $message->to($user->email)->subject('AgrifiMarket: '.$title);
            });
        } catch (\Throwable) {
            // The in-app alert still stands if mail is unavailable.
        }

        return $alert;
    }

    public function sendMany(iterable $users, string $title, string $body, ?string $url = null): void
    {
        $seen = [];
        foreach ($users as $user) {
            if (! $user instanceof User || isset($seen[$user->id])) {
                continue;
            }
            $seen[$user->id] = true;
            $this->send($user, $title, $body, $url);
        }
    }
}
