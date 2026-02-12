<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AiSuggestionResource;
use Illuminate\Http\Request;

class SuggestionController extends Controller
{
    public function index(Request $request)
    {
        return AiSuggestionResource::collection(
            $request->user()->aiSuggestions()->where('is_added', false)->latest('created_at')->get()
        );
    }

    public function generate(Request $request)
    {
        // TODO: OpenAI integration
        return response()->json(['message' => 'AI suggestion generation not yet implemented. Coming in Phase 2.']);
    }

    public function add(Request $request, $id)
    {
        $suggestion = $request->user()->aiSuggestions()->findOrFail($id);
        $suggestion->update(['is_added' => true]);

        $request->user()->actions()->create([
            'title' => $suggestion->title,
            'description' => $suggestion->description,
            'domain' => $suggestion->domain,
            'frequency' => 'daily',
            'alignment_rationale' => $suggestion->vision_connection,
        ]);

        return response()->json(['message' => 'Added to actions']);
    }
}
