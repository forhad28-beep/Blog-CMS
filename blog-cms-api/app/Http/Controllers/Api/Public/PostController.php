<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Tag;

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

    public function featured()
    {
        return Post::with('category:id,name')
            ->where('status', 'published')
            ->latest()
            ->take(5)
            ->get();
    }

    public function popular()
    {
        return Post::with('category:id,name')
            ->withCount('comments')
            ->where('status', 'published')
            ->orderByDesc('comments_count')
            ->take(5)
            ->get();
    }

    public function latest()
    {
        return Post::with('category:id,name')
            ->where('status', 'published')
            ->latest()
            ->take(5)
            ->get();
    }

    public function related($slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();

        return Post::where('status', 'published')
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->latest()
            ->take(4)
            ->get();
    }

    public function categories()
    {
        return Category::withCount([
            'posts' => function ($query) {
                $query->where('status', 'published');
            }
        ])
            ->orderBy('name')
            ->get();
    }

    public function tags()
    {
        return Tag::withCount([
            'posts' => function ($query) {
                $query->where('status', 'published');
            }
        ])
            ->orderBy('name')
            ->get();
    }
}
