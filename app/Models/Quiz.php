<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasUuid;
    protected $fillable = ['question','options','correct_index','pillar'];
    protected function casts(): array { return ['options'=>'array']; }
}
