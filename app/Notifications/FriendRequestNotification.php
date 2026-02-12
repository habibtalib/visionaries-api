<?php
namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class FriendRequestNotification extends Notification
{
    use Queueable;

    public function __construct(public User $sender) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'friend_request',
            'sender_id' => $this->sender->id,
            'sender_name' => $this->sender->name,
            'message' => $this->sender->name . ' sent you a friend request.',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
