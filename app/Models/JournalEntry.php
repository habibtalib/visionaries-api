<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    use HasUuid, SoftDeletes;
    protected $fillable = ['user_id','prompt','content','is_shared'];
    protected function casts(): array { return ['is_shared'=>'boolean']; }
    public function user() { return $this->belongsTo(User::class); }
}
