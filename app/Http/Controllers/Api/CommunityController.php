<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommunityPostResource;
use App\Http\Resources\CommunityCommentResource;
use App\Models\CommunityPost;
use App\Models\CommunityLike;
use App\Models\CommunityComment;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    public function feeds(Request $request)
    {
        return CommunityPostResource::collection(
            CommunityPost::with(['user', 'comments.user'])->latest()->paginate(20)
        );
    }

    public function createPost(Request $request)
    {
        $data = $request->validate(['content' => 'required|string']);
        $post = $request->user()->communityPosts()->create($data);
        return new CommunityPostResource($post->load('user'));
    }

    public function like(Request $request, $id)
    {
        $post = CommunityPost::findOrFail($id);
        $existing = CommunityLike::where('user_id', $request->user()->id)->where('post_id', $id)->first();

        if ($existing) {
            $existing->delete();
            $post->decrement('likes_count');
            return response()->json(['liked' => false]);
        }

        CommunityLike::create(['user_id' => $request->user()->id, 'post_id' => $id, 'created_at' => now()]);
        $post->increment('likes_count');
        return response()->json(['liked' => true]);
    }

    public function comment(Request $request, $id)
    {
        CommunityPost::findOrFail($id);
        $data = $request->validate(['content' => 'required|string']);
        $comment = CommunityComment::create([
            'user_id' => $request->user()->id,
            'post_id' => $id,
            'content' => $data['content'],
        ]);
        CommunityPost::where('id', $id)->increment('comments_count');
        return new CommunityCommentResource($comment->load('user'));
    }

    public function comments($id)
    {
        return CommunityCommentResource::collection(
            CommunityComment::where('post_id', $id)->with('user')->latest()->get()
        );
    }
}
