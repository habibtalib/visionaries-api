<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasUuid;
    public $timestamps = false;
    protected $fillable = ['user_id','category','answers','reflection_notes'];
    protected function casts(): array { return ['answers'=>'array','created_at'=>'datetime']; }
    public function user() { return $this->belongsTo(User::class); }
}
