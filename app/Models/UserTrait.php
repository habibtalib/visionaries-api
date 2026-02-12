<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class UserTrait extends Model
{
    use HasUuid;
    protected $fillable = ['user_id','trait_id','custom_name','why','daily','opposite','sort_order'];
    public function user() { return $this->belongsTo(User::class); }
    public function trait() { return $this->belongsTo(Trait_::class, 'trait_id'); }
}
