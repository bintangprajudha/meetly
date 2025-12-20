<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\RepostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminUserController;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

Route::get('/csrf-token', function () {
    return response()->json([
        'csrf_token' => csrf_token()
    ]);
})->middleware('web');

// Guest routes (only accessible when not logged in)
Route::middleware(['guest'])->group(function () {
    // Show login/register forms
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('show.login');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('show.register');

    // Handle form submissions
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
});

// Authenticated routes (only accessible when logged in)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $userId = Auth::id();

        $posts = \App\Models\Post::with('user')
            ->withCount(['likes', 'bookmarks', 'comments'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($post) use ($userId) {
                $post->liked = $post->likes()->where('user_id', $userId)->exists();
                $post->bookmarked = $post->bookmarks()->where('user_id', $userId)->exists();
                $post->replies_count = $post->comments_count;
                // Load empty comments array so count shows
                $post->comments = [];
                return $post;
            });

        return Inertia::render('Dashboard', [
            'user' => Auth::user(),
            'posts' => $posts,
        ]);
    })->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Post routes
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    // Follow and Unfollow
    Route::post('/users/{user}/follow', [FollowController::class, 'store'])->name('users.follow');
    Route::delete('/users/{user}/follow', [FollowController::class, 'destroy'])->name('users.unfollow');

    // Comment routes
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::get('/posts/{post}/comments/latest', [CommentController::class, 'latest'])->name('comments.latest');
    // Toggle like
    Route::post('/posts/{post}/like', [PostController::class, 'toggleLike'])->name('posts.like');
    // Toggle bookmark
    Route::post('/posts/{post}/bookmark', [PostController::class, 'toggleBookmark'])->name('posts.bookmark');
    Route::get('/bookmarks', [PostController::class, 'bookmarks'])->name('bookmarks.index');

    // Repost routes
    Route::post('/posts/{post}/repost', [RepostController::class, 'store'])->name('posts.repost');
    Route::delete('/reposts/{repost}', [RepostController::class, 'destroy'])->name('reposts.destroy');
    Route::get('/posts/{post}/reposts', [RepostController::class, 'getReposts'])->name('posts.reposts');
    Route::get('/posts/{post}/has-reposted', [RepostController::class, 'hasUserReposted'])->name('posts.has-reposted');
    Route::get('/chat/{user?}', [MessageController::class, 'index'])->name('messages.index');

    // "API" untuk frontend (session auth)
    Route::get('/api/messages', [MessageController::class, 'list'])->name('messages.list');
    Route::get('/api/messages/{user}', [MessageController::class, 'fetch'])->name('messages.fetch');
    Route::post('/api/messages', [MessageController::class, 'send'])->name('messages.send');
    Route::delete('/api/messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');
    Route::post('/api/messages/{user}/read', [MessageController::class, 'markAsRead'])->name('messages.read');
    Route::post('/api/messages/upload', [MessageController::class, 'uploadImage'])->name('messages.upload');
    Route::get('/api/chat/users', [MessageController::class, 'users'])->name('messages.users');
    Route::post('/api/posts/share', [MessageController::class, 'sharePost'])->name('posts.share');

    Route::get('/api/users', [UserController::class, 'apiIndex'])->name('users.api.index');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    Route::get('/{user:name}/followers', [UserController::class, 'followers'])->name('user.followers');
    Route::get('/{user:name}/following', [UserController::class, 'following'])->name('user.following');
    Route::get('/profile/edit', [UserController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [UserController::class, 'update'])
        ->name('profile.update')
        ->middleware('auth');

    // Report routes (user bisa melaporkan post)
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
});

// Admin routes (hanya admin yang bisa akses)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminUserController::class, 'dashboard'])->name('dashboard');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::patch('/reports/{report}/status', [ReportController::class, 'updateStatus'])->name('reports.update-status');
});

Route::get('/users/{user}/followers', [FollowController::class, 'followers'])->name('users.followers');
Route::get('/users/{user}/following', [FollowController::class, 'following'])->name('users.following');

// User Profile routes (accessible by both authenticated and guest users)
Route::get('/@{username}', [UserController::class, 'show'])
    ->name('user.profile')
    ->where('username', '[A-Za-z0-9_]+'); // Only allow alphanumeric and underscore
