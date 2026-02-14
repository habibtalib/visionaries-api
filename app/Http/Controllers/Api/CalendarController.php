<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IslamicEvent;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function events()
    {
        return IslamicEvent::orderBy('event_date')->get();
    }

    public function upcoming()
    {
        return IslamicEvent::where('event_date', '>=', Carbon::today())
            ->orderBy('event_date')
            ->limit(3)
            ->get();
    }
}