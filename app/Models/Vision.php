<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Vision extends Model
{
    use HasUuid;
    protected $fillable = ['user_id','akhirah_intention','future_world','legacy','generated_statement','be_statement'];
    public function user() { return $this->belongsTo(User::class); }
    public function versions() { return $this->hasMany(VisionVersion::class, 'user_id', 'user_id'); }
}
