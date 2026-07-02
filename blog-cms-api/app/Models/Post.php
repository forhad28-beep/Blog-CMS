<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'content',
        'featured_image',
        'status',
        'views',
        'published_at',
    ];
    protected $appends = ['featured_image_url'];

    public function getFeaturedImageUrlAttribute()
    {
        if (!$this->featured_image) {
            return null;
        }

        return asset('storage/' . $this->featured_image);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function scopeFilter($query, $request)
    {
        // Search
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Tag
        if ($request->filled('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('tags.id', $request->tag);
            });
        }

        // Sort
        if ($request->sort === 'oldest') {
            $query->oldest();
        } else {
            $query->latest();
        }

        return $query;
    }
}
