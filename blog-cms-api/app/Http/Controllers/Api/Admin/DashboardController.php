<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([

            'statistics' => [

                'users' => User::count(),

                'posts' => Post::count(),

                'published_posts' => Post::where(
                    'status',
                    'published'
                )->count(),

                'draft_posts' => Post::where(
                    'status',
                    'draft'
                )->count(),

                'categories' => Category::count(),

                'tags' => Tag::count(),

                'comments' => Comment::count(),

            ],

            'latest_posts' => Post::with('user:id,name')
                ->latest()
                ->take(5)
                ->get(),

            'latest_comments' => Comment::with([
                'user:id,name',
                'post:id,title'
            ])
                ->latest()
                ->take(5)
                ->get(),

            'monthly_posts' => Post::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total')
            )
                ->groupBy('month')
                ->orderBy('month')
                ->get(),

            'recent_activity' => Post::with('user:id,name')
                ->latest()
                ->take(10)
                ->get(['id', 'title', 'user_id', 'created_at']),
        ]);
    }
}