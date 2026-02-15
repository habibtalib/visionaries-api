<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushBroadcast;
use App\Models\PushSubscription;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;

class AdminPushController extends Controller
{
    public function broadcast(Request $request)
    {
        $request->validate([
            "title" => "required|string|max:255",
            "body" => "required|string|max:1000",
            "url" => "nullable|string|max:500",
        ]);

        $service = new PushNotificationService();
        $results = $service->sendToAll(
            $request->title,
            $request->body,
            array_filter(["url" => $request->url])
        );

        $sent = count(array_filter($results, fn($r) => $r["success"]));
        $failed = count($results) - $sent;

        $broadcast = PushBroadcast::create([
            "user_id" => $request->user()->id,
            "title" => $request->title,
            "body" => $request->body,
            "url" => $request->url,
            "sent_count" => $sent,
            "failed_count" => $failed,
            "sent_at" => now(),
        ]);

        return response()->json([
            "message" => "Broadcast sent",
            "broadcast" => $broadcast,
            "sent" => $sent,
            "failed" => $failed,
        ]);
    }

    public function broadcasts()
    {
        $broadcasts = PushBroadcast::orderBy("created_at", "desc")->limit(50)->get();
        return response()->json(["broadcasts" => $broadcasts]);
    }

    public function stats()
    {
        return response()->json([
            "total_subscriptions" => PushSubscription::count(),
            "unique_users" => PushSubscription::distinct("user_id")->count("user_id"),
        ]);
    }
}
