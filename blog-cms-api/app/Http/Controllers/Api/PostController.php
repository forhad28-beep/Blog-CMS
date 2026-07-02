<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Services\PostService;
class PostController extends Controller
{
    public function __construct(
        private PostService $postService
    ) {
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $posts = Post::with([
            'user:id,name',
            'category:id,name',
            'tags:id,name'
        ])
            ->withCount('comments')
            ->filter($request)
            ->paginate($perPage);

        return Post::with([
            'user',
            'category',
            'tags'
        ])
            ->withCount([
                'comments',
                'likes'
            ])
            ->latest()
            ->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        return $this->postService->create(
            $request->validated(),
            $request->user()
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return $post->load([
            'user',
            'category',
            'tags'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdatePostRequest $request,
        Post $post
    ) {
        $this->authorize('update', $post);

        return $this->postService->update(
            $post,
            $request->validated()
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $this->postService->delete($post);

        return response()->json([
            'message' => 'Post deleted successfully'
        ]);
    }
}
