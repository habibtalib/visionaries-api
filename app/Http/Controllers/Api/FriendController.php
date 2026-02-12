<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Friend;
use App\Models\User;
use Illuminate\Http\Request;

class FriendController extends Controller
{
    public function index(Request $request)
    {
        $friendIds = Friend::where(function($q) use ($request) {
            $q->where('user_id', $request->user()->id)->orWhere('friend_id', $request->user()->id);
        })->where('status', 'accepted')->get()->map(fn($f) => $f->user_id == $request->user()->id ? $f->friend_id : $f->user_id);

        return UserResource::collection(User::whereIn('id', $friendIds)->get());
    }

    public function requests(Request $request)
    {
        $pending = Friend::where('friend_id', $request->user()->id)->where('status', 'pending')->with('user')->get();
        return response()->json(['data' => $pending]);
    }

    public function sendRequest(Request $request)
    {
        $data = $request->validate(['friend_id' => 'required|exists:users,id']);
        Friend::create(['user_id' => $request->user()->id, 'friend_id' => $data['friend_id'], 'status' => 'pending']);
        event(new FriendRequestReceived($request->user(), $data['friend_id']));
        \App\Models\User::find($data['friend_id'])?->notify(new FriendRequestNotification($request->user()));
        return response()->json(['message' => 'Request sent']);
    }

    public function accept(Request $request, $id)
    {
        $friend = Friend::where('friend_id', $request->user()->id)->where('id', $id)->firstOrFail();
        $friend->update(['status' => 'accepted']);
        return response()->json(['message' => 'Accepted']);
    }

    public function decline(Request $request, $id)
    {
        Friend::where('friend_id', $request->user()->id)->where('id', $id)->firstOrFail()->delete();
        return response()->json(['message' => 'Declined']);
    }

    public function remove(Request $request, $id)
    {
        Friend::where(function($q) use ($request, $id) {
            $q->where(['user_id' => $request->user()->id, 'id' => $id])
              ->orWhere(['friend_id' => $request->user()->id, 'id' => $id]);
        })->delete();
        return response()->json(['message' => 'Removed']);
    }

    public function profile(Request $request, $id)
    {
        return new UserResource(User::findOrFail($id));
    }

    public function search(Request $request)
    {
        $q = $request->validate(['query' => 'required|string|min:2'])['query'];
        return UserResource::collection(User::where('name', 'ilike', "%$q%")->limit(20)->get());
    }
}
