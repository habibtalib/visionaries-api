<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasUuid;
    public $timestamps = false;
    protected $fillable = ['user_id','review_type','period_start','period_end','vision_reflection','identity_reflection','action_reflection','overall_reflection','domain_ratings'];
    protected function casts(): array { return ['period_start'=>'date','period_end'=>'date','domain_ratings'=>'array','created_at'=>'datetime']; }
    public function user() { return $this->belongsTo(User::class); }
}
