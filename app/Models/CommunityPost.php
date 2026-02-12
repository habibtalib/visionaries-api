<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunityPost extends Model
{
    use HasUuid, SoftDeletes;
    protected $fillable = ['user_id','content','type','is_flagged'];
    protected function casts(): array { return ['is_flagged'=>'boolean']; }
    public function user() { return $this->belongsTo(User::class); }
}
