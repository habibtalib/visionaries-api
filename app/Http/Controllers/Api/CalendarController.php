<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\IslamicEventResource;
use App\Models\IslamicEvent;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function events()
    {
        return IslamicEventResource::collection(IslamicEvent::orderBy('gregorian_date_2026')->get());
    }

    public function upcoming()
    {
        return IslamicEventResource::collection(
            IslamicEvent::where('gregorian_date_2026', '>=', Carbon::today())
                ->orderBy('gregorian_date_2026')
                ->limit(3)
                ->get()
        );
    }
}
