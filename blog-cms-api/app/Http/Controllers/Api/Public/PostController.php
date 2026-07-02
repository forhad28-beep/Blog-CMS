<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        return Post::with([
            'user:id,name',
            'category:id,name',
            'tags:id,name',
        ])
            ->withCount([
                'comments',
                'likes',
            ])
            ->where('status', 'published')

            ->when($request->search, function ($query) use ($request) {
                $query->where('title', 'like', "%{$request->search}%");
            })

            ->when($request->category_id, function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })

            ->when($request->tag_id, function ($query) use ($request) {
                $query->whereHas('tags', function ($q) use ($request) {
                    $q->where('tags.id', $request->tag_id);
                });
            })

            ->latest()
            ->paginate(10);
    }

    public function show($slug)
    {
        $post = Post::with([
            'user:id,name',
            'category:id,name',
            'tags:id,name',
            'comments.user:id,name',
        ])
            ->withCount([
                'comments',
                'likes',
            ])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return response()->json($post);
    }
}
