<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReelResource;
use App\Models\Reel;
use App\Models\ReelInteraction;
use Illuminate\Http\Request;

class ReelController extends Controller
{
    public function index()
    {
        return ReelResource::collection(Reel::where('is_active', true)->inRandomOrder()->paginate(10));
    }

    public function like(Request $request, $id)
    {
        $reel = Reel::findOrFail($id);
        $existing = ReelInteraction::where(['user_id' => $request->user()->id, 'reel_id' => $id, 'type' => 'like'])->first();

        if ($existing) {
            $existing->delete();
            $reel->decrement('likes_count');
            return response()->json(['liked' => false]);
        }

        ReelInteraction::create(['user_id' => $request->user()->id, 'reel_id' => $id, 'type' => 'like', 'created_at' => now()]);
        $reel->increment('likes_count');
        return response()->json(['liked' => true]);
    }

    public function save(Request $request, $id)
    {
        $reel = Reel::findOrFail($id);
        $existing = ReelInteraction::where(['user_id' => $request->user()->id, 'reel_id' => $id, 'type' => 'save'])->first();

        if ($existing) {
            $existing->delete();
            $reel->decrement('saves_count');
            return response()->json(['saved' => false]);
        }

        ReelInteraction::create(['user_id' => $request->user()->id, 'reel_id' => $id, 'type' => 'save', 'created_at' => now()]);
        $reel->increment('saves_count');
        return response()->json(['saved' => true]);
    }
}
