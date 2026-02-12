<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class CheckIn extends Model
{
    use HasUuid;
    protected $fillable = ['user_id','check_in_date','gratitude','struggle','dua','tawakkul_moment'];
    protected function casts(): array { return ['check_in_date'=>'date']; }
    public function user() { return $this->belongsTo(User::class); }
}
