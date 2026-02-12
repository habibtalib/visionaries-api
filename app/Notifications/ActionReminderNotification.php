<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ActionReminderNotification extends Notification
{
    use Queueable;

    public function __construct(public array $pendingActions) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'action_reminder',
            'pending_count' => count($this->pendingActions),
            'actions' => $this->pendingActions,
            'message' => 'You have ' . count($this->pendingActions) . ' actions to check in on today.',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
