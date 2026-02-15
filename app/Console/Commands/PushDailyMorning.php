<?php

namespace App\Console\Commands;

use App\Services\PushNotificationService;
use Illuminate\Console\Command;

class PushDailyMorning extends Command
{
    protected $signature = 'push:daily-morning';
    protected $description = 'Send daily morning push notification';

    public function handle()
    {
        $service = new PushNotificationService();
        $results = $service->sendToAll(
            'Good Morning 🌅',
            'Start your day with intention. What will you focus on today?',
            ['url' => '/']
        );
        $this->info('Sent ' . count($results) . ' notifications');
    }
}
