<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');


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
        $posts = \App\Models\Post::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

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

    // Comment routes
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/posts/{post}/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::get('/posts/{post}/comments', [CommentController::class, 'getByPost'])->name('comments.get');
});

// User Profile routes (accessible by both authenticated and guest users)
Route::get('/{username}', [UserController::class, 'show'])
    ->name('user.profile')
    ->where('username', '[A-Za-z0-9_]+'); // Only allow alphanumeric and underscore
