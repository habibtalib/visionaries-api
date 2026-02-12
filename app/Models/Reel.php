<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Reel extends Model
{
    use HasUuid;
    protected $fillable = ['content','content_ms','author','category','gradient','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
}
