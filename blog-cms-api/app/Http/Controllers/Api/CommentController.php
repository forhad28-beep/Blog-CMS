<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CommentResource::collection(
            Comment::with('user', 'post')
                ->latest()
                ->paginate(10)
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'post_id' => 'required|exists:posts,id',
            'comment' => 'required|string|max:1000',
        ]);

        $comment = Comment::create([
            'user_id' => $request->user()->id,
            'post_id' => $data['post_id'],
            'comment' => $data['comment'],
        ]);

        return response()->json(
            $comment->load('user', 'post'),
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        // return $comment->load('user', 'post');
        return new CommentResource(
            $comment->load('user', 'post')
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        $this->authorize('update', $comment);
        if ($request->user()->id !== $comment->user_id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $data = $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $comment->update($data);

        return $comment->load('user', 'post');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Comment $comment)
    {
        $this->authorize('delete', $comment);
        if ($request->user()->id !== $comment->user_id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully'
        ]);
    }

    public function postComments(Post $post)
    {
        return $post->comments()
            ->with('user')
            ->latest()
            ->paginate(10);
    }
}
