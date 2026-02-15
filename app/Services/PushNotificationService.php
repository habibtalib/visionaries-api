<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PushNotificationService
{
    private WebPush $webPush;

    public function __construct()
    {
        $auth = [
            'VAPID' => [
                'subject' => config('services.vapid.subject', env('VAPID_SUBJECT', 'mailto:admin@visionaries.pro')),
                'publicKey' => env('VAPID_PUBLIC_KEY'),
                'privateKey' => env('VAPID_PRIVATE_KEY'),
            ],
        ];

        $this->webPush = new WebPush($auth);
        $this->webPush->setAutomaticPadding(false);
    }

    public function sendToUser(User $user, string $title, string $body, array $data = []): array
    {
        $subscriptions = PushSubscription::where('user_id', $user->id)->get();
        $results = [];

        foreach ($subscriptions as $sub) {
            $subscription = Subscription::create([
                'endpoint' => $sub->endpoint,
                'publicKey' => $sub->p256dh_key,
                'authToken' => $sub->auth_token,
            ]);

            $payload = json_encode([
                'title' => $title,
                'body' => $body,
                'icon' => '/pwa-192x192.png',
                'badge' => '/pwa-192x192.png',
                'data' => $data,
            ]);

            $this->webPush->queueNotification($subscription, $payload);
        }

        foreach ($this->webPush->flush() as $report) {
            if ($report->isSuccess()) {
                $results[] = ['success' => true];
            } else {
                // Remove expired subscriptions
                PushSubscription::where('endpoint', $report->getEndpoint())->delete();
                $results[] = ['success' => false, 'reason' => $report->getReason()];
            }
        }

        return $results;
    }

    public function sendToAll(string $title, string $body, array $data = []): array
    {
        $subscriptions = PushSubscription::with('user')->get();
        $results = [];

        foreach ($subscriptions as $sub) {
            $subscription = Subscription::create([
                'endpoint' => $sub->endpoint,
                'publicKey' => $sub->p256dh_key,
                'authToken' => $sub->auth_token,
            ]);

            $payload = json_encode([
                'title' => $title,
                'body' => $body,
                'icon' => '/pwa-192x192.png',
                'badge' => '/pwa-192x192.png',
                'data' => $data,
            ]);

            $this->webPush->queueNotification($subscription, $payload);
        }

        foreach ($this->webPush->flush() as $report) {
            if ($report->isSuccess()) {
                $results[] = ['success' => true];
            } else {
                PushSubscription::where('endpoint', $report->getEndpoint())->delete();
                $results[] = ['success' => false, 'reason' => $report->getReason()];
            }
        }

        return $results;
    }
}
