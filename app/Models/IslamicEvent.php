<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class IslamicEvent extends Model
{
    use HasUuid;
    protected $fillable = ['title','title_ms','description','event_date','hijri_date','type','is_recurring'];
    protected function casts(): array { return ['event_date'=>'date','is_recurring'=>'boolean']; }
}
