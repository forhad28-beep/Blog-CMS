<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostService
{
    public function create(array $data, $user): Post
    {
        $slug = Str::slug($data['title']);

        if (Post::where('slug', $slug)->exists()) {
            $slug .= '-' . time();
        }

        $imagePath = null;

        if (request()->hasFile('featured_image')) {
            $imagePath = request()
                ->file('featured_image')
                ->store('posts', 'public');
        }

        $post = Post::create([
            'user_id' => $user->id,
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'slug' => $slug,
            'content' => $data['content'],
            'featured_image' => $imagePath,
            'status' => $data['status'],
            'published_at' => $data['status'] === 'published'
                ? now()
                : null,
        ]);

        $post->tags()->sync($data['tags'] ?? []);

        return $post->load('user', 'category', 'tags');
    }

    public function update(Post $post, array $data): Post
    {
        $slug = Str::slug($data['title']);

        if (
            Post::where('slug', $slug)
                ->where('id', '!=', $post->id)
                ->exists()
        ) {
            $slug .= '-' . time();
        }

        $imagePath = $post->featured_image;

        if (request()->hasFile('featured_image')) {

            if (
                $post->featured_image &&
                Storage::disk('public')->exists($post->featured_image)
            ) {
                Storage::disk('public')->delete($post->featured_image);
            }

            $imagePath = request()
                ->file('featured_image')
                ->store('posts', 'public');
        }

        $post->update([
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'slug' => $slug,
            'content' => $data['content'],
            'featured_image' => $imagePath,
            'status' => $data['status'],
            'published_at' => $data['status'] === 'published'
                ? now()
                : null,
        ]);

        $post->tags()->sync($data['tags'] ?? []);

        return $post->load('user', 'category', 'tags');
    }

    public function delete(Post $post): void
    {
        $post->tags()->detach();

        if (
            $post->featured_image &&
            Storage::disk('public')->exists($post->featured_image)
        ) {
            Storage::disk('public')->delete($post->featured_image);
        }

        $post->delete();
    }
}