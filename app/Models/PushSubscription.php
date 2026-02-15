<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{
    use HasUuid;

    protected $fillable = ['user_id', 'endpoint', 'p256dh_key', 'auth_token'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
