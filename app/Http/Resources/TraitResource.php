<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TraitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->localized_name,
            'description' => $this->localized_description,
            'why_template' => $this->localized_why_template,
            'daily_template' => $this->localized_daily_template,
            'opposite_template' => $this->localized_opposite_template,
            'category' => $this->category,
            'is_default' => $this->is_default,
            'is_custom' => $this->is_custom,
            'created_at' => $this->created_at,
            
            // Include original language fields for reference if needed
            'name_en' => $this->name,
            'name_ms' => $this->name_ms,
        ];
    }
}