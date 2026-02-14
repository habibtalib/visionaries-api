<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunityPost extends Model
{
    use HasUuid, SoftDeletes;
    
    protected $fillable = [
        'user_id', 'content', 'type', 'prompt_badge', 
        'likes_count', 'comments_count', 'shares_count', 'is_flagged'
    ];
    
    protected function casts(): array 
    { 
        return [
            'is_flagged' => 'boolean',
            'likes_count' => 'integer',
            'comments_count' => 'integer', 
            'shares_count' => 'integer'
        ]; 
    }
    
    public function user() 
    { 
        return $this->belongsTo(User::class); 
    }
    
    public function comments()
    {
        return $this->hasMany(CommunityComment::class, 'post_id');
    }
    
    public function likes()
    {
        return $this->hasMany(CommunityLike::class, 'post_id');
    }
}