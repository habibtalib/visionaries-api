<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class IslamicEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = App::getLocale();
        
        return [
            'id' => $this->id,
            'title' => $locale === 'ms' && $this->title_ms ? $this->title_ms : $this->title,
            'description' => $this->description,
            'event_date' => $this->event_date,
            'hijri_date' => $this->hijri_date,
            'type' => $this->type,
            'is_recurring' => $this->is_recurring,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Include original titles for reference
            'title_en' => $this->title,
            'title_ms' => $this->title_ms,
        ];
    }
}