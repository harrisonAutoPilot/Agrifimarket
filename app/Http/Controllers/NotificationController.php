<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $alerts = $request->user()->alerts()->latest()->paginate(20);

        return view('notifications.index', ['alerts' => $alerts]);
    }

    public function read(Request $request, \App\Models\AppNotification $alert)
    {
        abort_unless($alert->user_id === $request->user()->id, 403);
        $alert->update(['read_at' => now()]);

        return $alert->url ? redirect($alert->url) : back();
    }
}
