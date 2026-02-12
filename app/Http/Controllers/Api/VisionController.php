<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vision;
use App\Models\VisionVersion;
use App\Models\TimelineEvent;
use Illuminate\Http\Request;

class VisionController extends Controller
{
    public function show(Request $request)
    {
        $vision = $request->user()->vision;
        return response()->json($vision);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'akhirah_intention' => 'nullable|string',
            'future_world' => 'nullable|string',
            'legacy' => 'nullable|string',
            'generated_statement' => 'nullable|string',
            'be_statement' => 'nullable|string',
        ]);

        $user = $request->user();
        $vision = $user->vision;

        if ($vision) {
            // Create version snapshot before update
            $versionNumber = VisionVersion::where('user_id', $user->id)->max('version_number') ?? 0;
            VisionVersion::create([
                'user_id' => $user->id,
                'version_number' => $versionNumber + 1,
                'akhirah_intention' => $vision->akhirah_intention,
                'future_world' => $vision->future_world,
                'legacy' => $vision->legacy,
                'generated_statement' => $vision->generated_statement,
                'be_statement' => $vision->be_statement,
                'change_summary' => 'Updated vision',
            ]);
            $vision->update($data);
            $eventType = 'vision_updated';
        } else {
            $data['user_id'] = $user->id;
            $vision = Vision::create($data);
            $eventType = 'vision_created';
        }

        TimelineEvent::create([
            'user_id' => $user->id,
            'event_type' => $eventType,
            'category' => 'vision',
            'title' => $eventType === 'vision_created' ? 'Vision created' : 'Vision updated',
            'reference_id' => $vision->id,
        ]);

        return response()->json($vision->fresh());
    }

    public function history(Request $request)
    {
        $versions = $request->user()->visionVersions()
            ->orderBy('version_number', 'desc')
            ->get();
        return response()->json($versions);
    }
}
