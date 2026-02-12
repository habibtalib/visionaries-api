<?php
namespace App\Events;

use App\Models\Action;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActionCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public User $user, public Action $action, public string $status) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('friends.' . $this->user->id)];
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'action_title' => $this->action->title,
            'domain' => $this->action->domain,
            'status' => $this->status,
            'completed_at' => now()->toIso8601String(),
        ];
    }
}
