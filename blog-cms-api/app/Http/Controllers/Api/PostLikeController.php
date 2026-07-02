<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostLike;
use Illuminate\Http\Request;

class PostLikeController extends Controller
{
    public function toggle(Post $post, Request $request)
    {
        $like = PostLike::where('user_id', $request->user()->id)
            ->where('post_id', $post->id)
            ->first();

        if ($like) {
            $like->delete();

            return response()->json([
                'message' => 'Post unliked'
            ]);
        }

        PostLike::create([
            'user_id' => $request->user()->id,
            'post_id' => $post->id,
        ]);

        return response()->json([
            'message' => 'Post liked'
        ]);
    }
}
