<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Post::with([
            'user:id,name',
            'category:id,name',
            'tags:id,name'
        ])
            ->withCount('comments')
            ->latest()
            ->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|max:255',
            'content' => 'required',
            'status' => 'required|in:draft,published',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',

            'featured_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $slug = Str::slug($data['title']);

        $imagePath = null;

        if ($request->hasFile('featured_image')) {
            $imagePath = $request
                ->file('featured_image')
                ->store('posts', 'public');
        }

        $post = Post::create([
            'user_id' => $request->user()->id,
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'slug' => $slug,
            'content' => $data['content'],
            'status' => $data['status'],
            'published_at' => $data['status'] === 'published'
                ? now()
                : null,
            'featured_image' => $imagePath,
        ]);

        if (!empty($data['tags'])) {
            $post->tags()->sync($data['tags']);
        }

        return response()->json($post->load('tags'), 201);
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
    public function update(Request $request, Post $post)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|max:255',
            'content' => 'required',
            'status' => 'required|in:draft,published',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',

            'featured_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $slug = Str::slug($data['title']);

        if (
            Post::where('slug', $slug)
                ->where('id', '!=', $post->id)
                ->exists()
        ) {
            $slug .= '-' . time();
        }

        $imagePath = $post->featured_image;

        if ($request->hasFile('featured_image')) {

            if (
                $post->featured_image &&
                Storage::disk('public')->exists($post->featured_image)
            ) {
                Storage::disk('public')->delete($post->featured_image);
            }

            $imagePath = $request
                ->file('featured_image')
                ->store('posts', 'public');
        }

        $post->update([
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'slug' => $slug,
            'content' => $data['content'],
            'status' => $data['status'],
            'published_at' => $data['status'] === 'published'
                ? now()
                : null,
            'featured_image' => $imagePath,
        ]);

        $post->tags()->sync($data['tags'] ?? []);

        return $post->load('tags');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $post->tags()->detach();

        if (
            $post->featured_image &&
            Storage::disk('public')->exists($post->featured_image)
        ) {
            Storage::disk('public')->delete($post->featured_image);
        }

        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully'
        ]);
    }
}
