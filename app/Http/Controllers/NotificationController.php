<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function read(Request $request, string $notification)
    {
        $notification = $this->notificationForCurrentUser($notification);
        $notification->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }

    public function open(string $notification)
    {
        $notification = $this->notificationForCurrentUser($notification);
        $notification->markAsRead();

        return redirect((string) data_get($notification->data, 'url', route('Index')));
    }

    private function notificationForCurrentUser(string $notification)
    {
        $notifiables = collect([
            auth()->user(),
            Auth::guard('customers')->user(),
        ])->filter();

        abort_unless($notifiables->isNotEmpty(), 403);

        foreach ($notifiables as $notifiable) {
            $matchedNotification = $notifiable->notifications()
                ->where('id', $notification)
                ->first();

            if ($matchedNotification) {
                return $matchedNotification;
            }
        }

        abort(404);
    }
}
