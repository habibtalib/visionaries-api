<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class PushBroadcast extends Model
{
    use HasUuid;

    protected $fillable = ["user_id", "title", "body", "url", "sent_count", "failed_count", "sent_at"];

    protected function casts(): array
    {
        return ["sent_at" => "datetime"];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
