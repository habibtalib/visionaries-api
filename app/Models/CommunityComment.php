<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunityComment extends Model
{
    use HasUuid, SoftDeletes;
    
    protected $fillable = ['user_id', 'post_id', 'content'];
    
    public function user() 
    { 
        return $this->belongsTo(User::class); 
    }
    
    public function post()
    {
        return $this->belongsTo(CommunityPost::class, 'post_id');
    }
}