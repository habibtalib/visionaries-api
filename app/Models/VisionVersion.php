<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class VisionVersion extends Model
{
    use HasUuid;
    public $timestamps = false;
    protected $fillable = ['user_id','version_number','akhirah_intention','future_world','legacy','generated_statement','be_statement','change_summary'];
    protected function casts(): array { return ['created_at' => 'datetime']; }
    public function user() { return $this->belongsTo(User::class); }
}
