<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Trait_ extends Model
{
    use HasUuid;
    protected $table = 'traits';
    public $timestamps = false;
    protected $fillable = ['name','description','why_template','daily_template','opposite_template','category','is_default','is_custom'];
    protected function casts(): array { return ['is_default'=>'boolean','is_custom'=>'boolean']; }
    public function userTraits() { return $this->hasMany(UserTrait::class, 'trait_id'); }
}
