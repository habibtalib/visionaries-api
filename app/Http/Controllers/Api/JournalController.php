<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Models\TimelineEvent;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    public function index(Request $request)
    {
        $entries = $request->user()->journalEntries()
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));
        return response()->json($entries);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'prompt' => 'nullable|string',
            'content' => 'required|string',
        ]);
        $data['user_id'] = $request->user()->id;
        $entry = JournalEntry::create($data);

        TimelineEvent::create([
            'user_id' => $request->user()->id, 'event_type' => 'journal_written',
            'category' => 'journal', 'title' => 'Journal entry written',
            'reference_id' => $entry->id,
        ]);

        return response()->json($entry, 201);
    }

    public function show(Request $request, string $id)
    {
        return response()->json($request->user()->journalEntries()->findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $entry = $request->user()->journalEntries()->findOrFail($id);
        $data = $request->validate(['prompt' => 'nullable|string', 'content' => 'sometimes|string']);
        $entry->update($data);
        return response()->json($entry);
    }

    public function destroy(Request $request, string $id)
    {
        $request->user()->journalEntries()->findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
