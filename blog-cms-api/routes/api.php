<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\Public\PostController as publicPostController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\PostLikeController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\Public\ContactController;
use App\Http\Controllers\Api\Public\NewsletterController;
use App\Http\Controllers\Api\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Api\Admin\NewsletterController as AdminNewsletterController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/posts/{post}/comments', [CommentController::class, 'postComments']);

// public routes
Route::prefix('public')->group(function () {
    Route::get('/posts', [publicPostController::class, 'index']);
    Route::get('/posts/{slug}', [publicPostController::class, 'show']);

    Route::get('/featured-posts', [publicPostController::class, 'featured']);
    Route::get('/popular-posts', [publicPostController::class, 'popular']);
    Route::get('/latest-posts', [publicPostController::class, 'latest']);
    Route::get('/posts/{slug}/related', [publicPostController::class, 'related']);
    Route::get('/categories', [publicPostController::class, 'categories']);
    Route::get('/tags', [publicPostController::class, 'tags']);

    Route::post('/contact', [ContactController::class, 'store']);
    Route::post('/newsletter', [NewsletterController::class, 'store']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', function (Request $request) {
        return $request->user();
    });

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'changePassword']);

    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('tags', TagController::class);
    Route::apiResource('posts', PostController::class);
    Route::apiResource('comments', CommentController::class);

    Route::post(
        '/posts/{post}/like',
        [PostLikeController::class, 'toggle']
    );

    Route::post(
        '/posts/{post}/bookmark',
        [BookmarkController::class, 'toggle']
    );
    Route::get(
        '/bookmarks',
        [BookmarkController::class, 'index']
    );
});


Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::put('/users/{user}/role', [UserController::class, 'updateRole']);

    Route::get('/contacts', [AdminContactController::class, 'index']);
    Route::get('/newsletters', [AdminNewsletterController::class, 'index']);
});