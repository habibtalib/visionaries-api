<?php
namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CheckInReminder implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public User $user, public array $pendingActions) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.' . $this->user->id)];
    }

    public function broadcastWith(): array
    {
        return [
            'message' => 'Time to check in on your daily actions!',
            'pending_count' => count($this->pendingActions),
            'actions' => $this->pendingActions,
        ];
    }
}
