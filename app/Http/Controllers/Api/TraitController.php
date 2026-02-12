<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trait_;
use App\Models\UserTrait;
use App\Models\TimelineEvent;
use Illuminate\Http\Request;

class TraitController extends Controller
{
    public function library()
    {
        return response()->json(Trait_::where('is_default', true)->get());
    }

    public function mine(Request $request)
    {
        $traits = $request->user()->userTraits()
            ->with('trait')
            ->orderBy('sort_order')
            ->get();
        return response()->json($traits);
    }

    public function addMine(Request $request)
    {
        $data = $request->validate([
            'trait_id' => 'nullable|uuid|exists:traits,id',
            'custom_name' => 'nullable|string|max:50',
            'why' => 'nullable|string',
            'daily' => 'nullable|string',
            'opposite' => 'nullable|string',
            'sort_order' => 'sometimes|integer',
        ]);

        $user = $request->user();
        $count = $user->userTraits()->count();
        if ($count >= 7) {
            return response()->json(['message' => 'Maximum 7 traits allowed'], 422);
        }

        $data['user_id'] = $user->id;
        $ut = UserTrait::create($data);

        TimelineEvent::create([
            'user_id' => $user->id, 'event_type' => 'trait_added',
            'category' => 'identity', 'title' => 'Added trait: ' . ($ut->custom_name ?? $ut->trait?->name),
            'reference_id' => $ut->id,
        ]);

        return response()->json($ut->load('trait'), 201);
    }

    public function updateMine(Request $request, string $id)
    {
        $ut = $request->user()->userTraits()->findOrFail($id);
        $data = $request->validate([
            'why' => 'nullable|string',
            'daily' => 'nullable|string',
            'opposite' => 'nullable|string',
            'sort_order' => 'sometimes|integer',
        ]);
        $ut->update($data);
        return response()->json($ut->load('trait'));
    }

    public function removeMine(Request $request, string $id)
    {
        $ut = $request->user()->userTraits()->findOrFail($id);
        $user = $request->user();
        if ($user->userTraits()->count() <= 3) {
            return response()->json(['message' => 'Minimum 3 traits required'], 422);
        }

        TimelineEvent::create([
            'user_id' => $user->id, 'event_type' => 'trait_removed',
            'category' => 'identity', 'title' => 'Removed trait: ' . ($ut->custom_name ?? $ut->trait?->name),
        ]);

        $ut->delete();
        return response()->json(['message' => 'Removed']);
    }

    public function createCustom(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
        ]);
        $data['is_default'] = false;
        $data['is_custom'] = true;
        $trait = Trait_::create($data);
        return response()->json($trait, 201);
    }
}
