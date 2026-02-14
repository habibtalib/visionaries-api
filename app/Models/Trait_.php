<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Trait_ extends Model
{
    use HasUuid;
    protected $table = 'traits';
    public $timestamps = false;
    protected $fillable = [
        'name','description','why_template','daily_template','opposite_template','category','is_default','is_custom',
        'name_ms','description_ms','why_template_ms','daily_template_ms','opposite_template_ms'
    ];
    
    protected function casts(): array { 
        return ['is_default'=>'boolean','is_custom'=>'boolean']; 
    }
    
    public function userTraits() { 
        return $this->hasMany(UserTrait::class, 'trait_id'); 
    }
    
    /**
     * Get localized name
     */
    public function getLocalizedNameAttribute()
    {
        $locale = App::getLocale();
        return $locale === 'ms' && $this->name_ms ? $this->name_ms : $this->name;
    }
    
    /**
     * Get localized description
     */
    public function getLocalizedDescriptionAttribute()
    {
        $locale = App::getLocale();
        return $locale === 'ms' && $this->description_ms ? $this->description_ms : $this->description;
    }
    
    /**
     * Get localized why template
     */
    public function getLocalizedWhyTemplateAttribute()
    {
        $locale = App::getLocale();
        return $locale === 'ms' && $this->why_template_ms ? $this->why_template_ms : $this->why_template;
    }
    
    /**
     * Get localized daily template
     */
    public function getLocalizedDailyTemplateAttribute()
    {
        $locale = App::getLocale();
        return $locale === 'ms' && $this->daily_template_ms ? $this->daily_template_ms : $this->daily_template;
    }
    
    /**
     * Get localized opposite template
     */
    public function getLocalizedOppositeTemplateAttribute()
    {
        $locale = App::getLocale();
        return $locale === 'ms' && $this->opposite_template_ms ? $this->opposite_template_ms : $this->opposite_template;
    }
}