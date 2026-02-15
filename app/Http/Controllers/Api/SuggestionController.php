<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Action;
use Illuminate\Http\Request;

class SuggestionController extends Controller
{
    private static array $suggestions = [
        ['id' => 1, 'title' => 'Write a letter of wisdom to your future grandchildren', 'description' => 'Document your life lessons, values, and hopes for future generations.', 'domain' => 'Family', 'category' => 'Legacy', 'impact' => 'high', 'effort' => 'medium', 'visionConnection' => 'Leaving behind knowledge and values that continue to inspire long after you\'re gone.', 'aiReasoning' => 'Your vision emphasizes lasting impact. Letter-writing is a high-emotional-value action that directly builds your legacy pillar.'],
        ['id' => 2, 'title' => 'Mentor a young person in your community', 'description' => 'Share your expertise and life experiences with someone who can benefit from your guidance.', 'domain' => 'Community', 'category' => 'Community', 'impact' => 'high', 'effort' => 'high', 'visionConnection' => 'Being a source of light and guidance in your community.', 'aiReasoning' => 'Your community-focused traits make mentoring a natural fit.'],
        ['id' => 3, 'title' => 'Create a weekly family learning circle', 'description' => 'Gather family for shared learning and bonding around meaningful topics.', 'domain' => 'Family', 'category' => 'Family', 'impact' => 'high', 'effort' => 'medium', 'visionConnection' => 'Raising righteous, knowledgeable children.', 'aiReasoning' => 'Combines your family vision with your love of learning.'],
        ['id' => 4, 'title' => 'Start a morning gratitude journal', 'description' => 'Begin each day by writing three things you\'re grateful for.', 'domain' => 'Spiritual', 'category' => 'Spiritual', 'impact' => 'medium', 'effort' => 'low', 'visionConnection' => 'Deepening your connection with the Divine through daily recognition of blessings.', 'aiReasoning' => 'Low effort, high consistency potential.'],
        ['id' => 5, 'title' => 'Volunteer at a local education initiative', 'description' => 'Contribute your time and skills to educational programs.', 'domain' => 'Community', 'category' => 'Community', 'impact' => 'high', 'effort' => 'medium', 'visionConnection' => 'Serving the ummah with patience.', 'aiReasoning' => 'Education volunteering creates compounding impact.'],
        ['id' => 6, 'title' => 'Document your life lessons in a journal', 'description' => 'Regular reflection and documentation of insights.', 'domain' => 'Learning', 'category' => 'Legacy', 'impact' => 'medium', 'effort' => 'low', 'visionConnection' => 'Building a repository of wisdom that outlives you.', 'aiReasoning' => 'Low-barrier entry to legacy building.'],
        ['id' => 7, 'title' => 'Teach a skill you\'ve mastered to others', 'description' => 'Share your expertise through workshops or teaching sessions.', 'domain' => 'Learning', 'category' => 'Growth', 'impact' => 'high', 'effort' => 'medium', 'visionConnection' => 'Passing knowledge to the next generation.', 'aiReasoning' => 'Teaching is the highest-leverage growth activity.'],
        ['id' => 8, 'title' => 'Create a family traditions document', 'description' => 'Record and establish meaningful traditions.', 'domain' => 'Family', 'category' => 'Family', 'impact' => 'medium', 'effort' => 'low', 'visionConnection' => 'Building a strong family foundation.', 'aiReasoning' => 'Quick win with lasting emotional value.'],
        ['id' => 9, 'title' => 'Join or start a community improvement project', 'description' => 'Take initiative in addressing local needs.', 'domain' => 'Community', 'category' => 'Community', 'impact' => 'high', 'effort' => 'high', 'visionConnection' => 'Making a tangible difference in your community.', 'aiReasoning' => 'High visibility and impact.'],
        ['id' => 10, 'title' => 'Develop a personal growth reading list', 'description' => 'Curate books aligned with your vision.', 'domain' => 'Learning', 'category' => 'Growth', 'impact' => 'medium', 'effort' => 'low', 'visionConnection' => 'Continuous self-improvement through curated knowledge.', 'aiReasoning' => 'Low effort, flexible timing.'],
        ['id' => 11, 'title' => 'Establish a weekly tahajjud practice', 'description' => 'Wake before Fajr at least once a week for night prayer.', 'domain' => 'Spiritual', 'category' => 'Spiritual', 'impact' => 'high', 'effort' => 'medium', 'visionConnection' => 'The closest a servant is to Allah is in the last third of the night.', 'aiReasoning' => 'Starting weekly makes it sustainable.'],
        ['id' => 12, 'title' => 'Take a 30-minute daily walk in nature', 'description' => 'Disconnect from screens and reconnect with creation.', 'domain' => 'Health', 'category' => 'Growth', 'impact' => 'medium', 'effort' => 'low', 'visionConnection' => 'A sound body supports a sound mind and spirit.', 'aiReasoning' => 'Walking reduces stress and creates space for reflection.'],
    ];

    public function index(Request $request)
    {
        $user = $request->user();
        // Filter out suggestions user already has as actions
        $existingTitles = $user->actions()->pluck('title')->map(fn($t) => strtolower($t))->toArray();

        $suggestions = collect(self::$suggestions)->filter(function ($s) use ($existingTitles) {
            return !in_array(strtolower($s['title']), $existingTitles);
        })->values();

        if ($request->has('category') && $request->category !== 'all') {
            $suggestions = $suggestions->where('category', $request->category)->values();
        }

        return response()->json($suggestions);
    }

    public function add(Request $request, $id)
    {
        $suggestion = collect(self::$suggestions)->firstWhere('id', (int)$id);
        if (!$suggestion) {
            return response()->json(['message' => 'Suggestion not found'], 404);
        }

        $domainMap = ['Spiritual' => 'spiritual', 'Learning' => 'knowledge', 'Family' => 'family', 'Health' => 'health', 'Community' => 'community', 'Work' => 'professional'];

        $action = $request->user()->actions()->create([
            'title' => $suggestion['title'],
            'description' => $suggestion['description'],
            'domain' => $domainMap[$suggestion['domain']] ?? 'spiritual',
            'frequency' => 'daily',
            'alignment' => $suggestion['visionConnection'],
        ]);

        return response()->json(['message' => 'Added to actions', 'action' => $action], 201);
    }
}
