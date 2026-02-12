<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ActionCheckIn extends Model
{
    use HasUuid;
    public $timestamps = false;
    protected $fillable = ['action_id','user_id','check_in_date','status','reflection','mood','energy_level'];
    protected function casts(): array { return ['check_in_date'=>'date','created_at'=>'datetime']; }
    public function action() { return $this->belongsTo(Action::class); }
    public function user() { return $this->belongsTo(User::class); }
}
