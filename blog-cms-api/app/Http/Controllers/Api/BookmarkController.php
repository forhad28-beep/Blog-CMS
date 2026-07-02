<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\Post;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function toggle(Post $post, Request $request)
    {
        $bookmark = Bookmark::where('user_id', $request->user()->id)
            ->where('post_id', $post->id)
            ->first();

        if ($bookmark) {
            $bookmark->delete();

            return response()->json([
                'message' => 'Bookmark removed'
            ]);
        }

        Bookmark::create([
            'user_id' => $request->user()->id,
            'post_id' => $post->id,
        ]);

        return response()->json([
            'message' => 'Post bookmarked'
        ]);
    }

    public function index(Request $request)
    {
        return Bookmark::with([
            'post.user',
            'post.category',
            'post.tags',
        ])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);
    }
}
