<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class CommunityLike extends Model
{
    use HasUuid;
    
    protected $fillable = ['user_id', 'post_id'];
    
    public function user() 
    { 
        return $this->belongsTo(User::class); 
    }
    
    public function post()
    {
        return $this->belongsTo(CommunityPost::class, 'post_id');
    }
}