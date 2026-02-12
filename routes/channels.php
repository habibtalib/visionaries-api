<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('friends.{id}', function ($user, $id) {
    // Allow if user is friends with this person
    return \App\Models\Friend::where(function ($q) use ($user, $id) {
        $q->where(['user_id' => $user->id, 'friend_id' => $id])
          ->orWhere(['user_id' => $id, 'friend_id' => $user->id]);
    })->where('status', 'accepted')->exists();
});

Broadcast::channel('community', function ($user) {
    return $user !== null;
});
