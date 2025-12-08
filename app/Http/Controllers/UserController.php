<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserController extends Controller
{
    public function show($username)
    {
        // Find user by username
        $user = User::where('name', $username)->firstOrFail();

        // Get user's posts with eager loading
        $posts = Post::with('user')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('UserProfile', [
            'profileUser' => $user,
            'posts' => $posts,
            'postsCount' => $posts->count(),
            'isFollowing' => auth()->check()
                ? auth()->user()->isFollowing($user)
                : false,
            'auth' => [
                'user' => Auth::user()
            ]
        ]);
    }
}
