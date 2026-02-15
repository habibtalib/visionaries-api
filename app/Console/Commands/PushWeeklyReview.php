<?php

namespace App\Console\Commands;

use App\Services\PushNotificationService;
use Illuminate\Console\Command;

class PushWeeklyReview extends Command
{
    protected $signature = 'push:weekly-review';
    protected $description = 'Send weekly review push notification';

    public function handle()
    {
        $service = new PushNotificationService();
        $results = $service->sendToAll(
            'Weekly Review 📊',
            'Time for your weekly review. Reflect on your progress and plan ahead.',
            ['url' => '/reviews']
        );
        $this->info('Sent ' . count($results) . ' notifications');
    }
}
