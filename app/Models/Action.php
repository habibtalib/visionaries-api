<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Action extends Model
{
    use HasUuid, SoftDeletes;
    protected $fillable = ['user_id','title','description','domain','frequency','alignment','scheduled_time','is_active','sort_order'];
    protected function casts(): array { return ['is_active'=>'boolean','scheduled_time'=>'datetime:H:i']; }
    public function user() { return $this->belongsTo(User::class); }
    public function checkIns() { return $this->hasMany(ActionCheckIn::class); }
}
