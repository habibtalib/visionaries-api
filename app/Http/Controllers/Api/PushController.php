<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;

class PushController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        $user = $request->user();

        PushSubscription::updateOrCreate(
            ['user_id' => $user->id, 'endpoint' => $request->endpoint],
            [
                'p256dh_key' => $request->input('keys.p256dh'),
                'auth_token' => $request->input('keys.auth'),
            ]
        );

        return response()->json(['message' => 'Subscribed successfully']);
    }

    public function unsubscribe(Request $request)
    {
        $request->validate(['endpoint' => 'required|string']);

        PushSubscription::where('user_id', $request->user()->id)
            ->where('endpoint', $request->endpoint)
            ->delete();

        return response()->json(['message' => 'Unsubscribed successfully']);
    }

    public function test(Request $request)
    {
        $service = new PushNotificationService();
        $results = $service->sendToUser(
            $request->user(),
            'Visionaries Pro 🌟',
            'Push notifications are working! Keep building your vision.',
            ['url' => '/']
        );

        return response()->json(['results' => $results]);
    }

    public function vapidKey()
    {
        return response()->json(['key' => env('VAPID_PUBLIC_KEY')]);
    }
}
