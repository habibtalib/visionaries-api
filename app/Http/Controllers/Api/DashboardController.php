<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActionCheckIn;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function today(Request $request)
    {
        $user = $request->user();
        $today = now()->toDateString();

        $vision = $user->vision;
        $dueActions = $user->actions()->where('is_active', true)->where('frequency', 'daily')->get();
        $checkIns = ActionCheckIn::where('user_id', $user->id)->where('check_in_date', $today)->get()->keyBy('action_id');
        $todayCheckIn = $user->checkIns()->where('check_in_date', $today)->first();

        return response()->json([
            'vision_preview' => $vision?->generated_statement,
            'due_actions' => $dueActions->map(fn($a) => array_merge($a->toArray(), [
                'today_status' => $checkIns->get($a->id)?->status,
            ])),
            'completed_count' => $checkIns->where('status', 'done')->count(),
            'total_due' => $dueActions->count(),
            'daily_check_in' => $todayCheckIn,
        ]);
    }
}
