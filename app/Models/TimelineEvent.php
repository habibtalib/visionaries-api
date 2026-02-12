<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class TimelineEvent extends Model
{
    use HasUuid;
    public $timestamps = false;
    protected $fillable = ['user_id','event_type','category','title','description','reference_id'];
    protected function casts(): array { return ['created_at'=>'datetime']; }
    public function user() { return $this->belongsTo(User::class); }
}
