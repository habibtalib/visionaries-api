<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\TimelineEvent;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->reviews();
        if ($request->has('type')) $query->where('review_type', $request->type);
        return response()->json($query->orderBy('period_start', 'desc')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'review_type' => 'required|string|in:weekly,monthly,quarterly,annual',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'vision_reflection' => 'nullable|string',
            'identity_reflection' => 'nullable|string',
            'action_reflection' => 'nullable|string',
            'overall_reflection' => 'nullable|string',
            'domain_ratings' => 'nullable|array',
        ]);
        $data['user_id'] = $request->user()->id;

        $review = Review::updateOrCreate(
            ['user_id' => $data['user_id'], 'review_type' => $data['review_type'], 'period_start' => $data['period_start']],
            $data
        );

        TimelineEvent::create([
            'user_id' => $request->user()->id, 'event_type' => 'review_completed',
            'category' => 'review', 'title' => ucfirst($data['review_type']) . ' review completed',
            'reference_id' => $review->id,
        ]);

        return response()->json($review, 201);
    }

    public function monthly(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $start = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $review = $request->user()->reviews()
            ->where('review_type', 'monthly')
            ->where('period_start', $start->toDateString())
            ->first();

        $checkInStats = $request->user()->actionCheckIns()
            ->whereBetween('check_in_date', [$start, $end])
            ->get()
            ->groupBy('status')
            ->map->count();

        return response()->json([
            'review' => $review,
            'stats' => $checkInStats,
            'period' => ['start' => $start->toDateString(), 'end' => $end->toDateString()],
        ]);
    }
}
