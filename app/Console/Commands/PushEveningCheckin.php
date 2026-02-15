<?php

namespace App\Console\Commands;

use App\Services\PushNotificationService;
use Illuminate\Console\Command;

class PushEveningCheckin extends Command
{
    protected $signature = 'push:evening-checkin';
    protected $description = 'Send evening check-in push notification';

    public function handle()
    {
        $service = new PushNotificationService();
        $results = $service->sendToAll(
            'Evening Reflection 🤲',
            'How was your day? Take a moment to reflect and check in.',
            ['url' => '/check-in']
        );
        $this->info('Sent ' . count($results) . ' notifications');
    }
}
