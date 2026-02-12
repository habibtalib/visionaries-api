<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Action;
use App\Events\ActionCompleted;
use App\Models\ActionCheckIn;
use App\Models\TimelineEvent;
use Illuminate\Http\Request;

class ActionController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->actions();
        if ($request->has('domain')) $query->where('domain', $request->domain);
        if ($request->has('frequency')) $query->where('frequency', $request->frequency);
        if ($request->has('is_active')) $query->where('is_active', $request->boolean('is_active'));
        return response()->json($query->orderBy('sort_order')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'domain' => 'required|string|in:spiritual,knowledge,family,health,professional,community',
            'frequency' => 'required|string|in:daily,weekly,monthly,yearly,lifetime',
            'alignment' => 'required|string|min:1',
            'scheduled_time' => 'nullable|date_format:H:i',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
        ]);
        $data['user_id'] = $request->user()->id;

        // Overload warning
        $dailyCount = $request->user()->actions()->where('frequency', 'daily')->where('is_active', true)->count();
        $warning = $dailyCount >= 7 ? 'You have many daily actions. Consider if this is sustainable.' : null;

        $action = Action::create($data);

        TimelineEvent::create([
            'user_id' => $request->user()->id, 'event_type' => 'action_created',
            'category' => 'actions', 'title' => 'Created action: ' . $action->title,
            'reference_id' => $action->id,
        ]);

        $response = ['action' => $action];
        if ($warning) $response['warning'] = $warning;
        return response()->json($response, 201);
    }

    public function update(Request $request, string $id)
    {
        $action = $request->user()->actions()->findOrFail($id);
        $data = $request->validate([
            'title' => 'sometimes|string|max:200',
            'description' => 'nullable|string',
            'domain' => 'sometimes|string|in:spiritual,knowledge,family,health,professional,community',
            'frequency' => 'sometimes|string|in:daily,weekly,monthly,yearly,lifetime',
            'alignment' => 'sometimes|string|min:1',
            'scheduled_time' => 'nullable|date_format:H:i',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
        ]);
        $action->update($data);
        return response()->json($action);
    }

    public function destroy(Request $request, string $id)
    {
        $action = $request->user()->actions()->findOrFail($id);
        $action->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function checkIn(Request $request, string $id)
    {
        $action = $request->user()->actions()->findOrFail($id);
        $data = $request->validate([
            'date' => 'required|date',
            'status' => 'required|string|in:done,missed,reflected',
            'reflection' => 'nullable|string',
            'mood' => 'nullable|string|max:20',
            'energy_level' => 'nullable|integer|min:1|max:10',
        ]);

        $checkIn = ActionCheckIn::updateOrCreate(
            ['action_id' => $action->id, 'check_in_date' => $data['date']],
            [
                'user_id' => $request->user()->id,
                'status' => $data['status'],
                'reflection' => $data['reflection'] ?? null,
                'mood' => $data['mood'] ?? null,
                'energy_level' => $data['energy_level'] ?? null,
            ]
        );

        $eventType = $data['status'] === 'done' ? 'action_completed' : ($data['status'] === 'reflected' ? 'action_reflected' : null);
        if ($eventType) {
            TimelineEvent::create([
                'user_id' => $request->user()->id, 'event_type' => $eventType,
                'category' => 'actions', 'title' => ucfirst($data['status']) . ': ' . $action->title,
                'reference_id' => $action->id,
            ]);
        }

        event(new ActionCompleted($request->user(), $action, $data["status"]));
        return response()->json($checkIn);
    }

    public function today(Request $request)
    {
        $today = now()->toDateString();
        $actions = $request->user()->actions()
            ->where('is_active', true)
            ->where('frequency', 'daily')
            ->get();

        $checkIns = ActionCheckIn::where('user_id', $request->user()->id)
            ->where('check_in_date', $today)
            ->get()
            ->keyBy('action_id');

        $result = $actions->map(fn($a) => array_merge($a->toArray(), [
            'today_status' => $checkIns->get($a->id)?->status,
            'today_reflection' => $checkIns->get($a->id)?->reflection,
        ]));

        return response()->json($result);
    }

    public function stats(Request $request)
    {
        $user = $request->user();
        $stats = $user->actions()
            ->where('is_active', true)
            ->get()
            ->groupBy('domain')
            ->map(fn($actions) => [
                'total' => $actions->count(),
                'action_ids' => $actions->pluck('id'),
            ]);
        return response()->json($stats);
    }
}
