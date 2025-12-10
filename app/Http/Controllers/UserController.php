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
        $user = User::where('name', $username)
            ->withCount(['followers', 'following']) 
            ->firstOrFail();

        /** @var \App\Models\User|null $authUser */
        $authUser = Auth::user();
        $authUserId = $authUser ? $authUser->id : null;

        // Get user's posts with eager loading and counts
        $posts = Post::with('user')
            ->where('user_id', $user->id)
            ->withCount(['likes', 'bookmarks', 'comments'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($post) use ($authUserId) {
                if ($authUserId) {
                    $post->liked = $post->likes()->where('user_id', $authUserId)->exists();
                    $post->bookmarked = $post->bookmarks()->where('user_id', $authUserId)->exists();
                } else {
                    $post->liked = false;
                    $post->bookmarked = false;
                }
                $post->replies_count = $post->comments_count;
                return $post;
            });

        // Get liked posts with proper counts and status
        $likedPosts = $user->likes()
            ->with('user')
            ->withCount(['likes', 'bookmarks', 'comments'])
            ->latest()
            ->get()
            ->map(function ($post) use ($authUserId) {
                if ($authUserId) {
                    $post->liked = $post->likes()->where('user_id', $authUserId)->exists();
                    $post->bookmarked = $post->bookmarks()->where('user_id', $authUserId)->exists();
                } else {
                    $post->liked = false;
                    $post->bookmarked = false;
                }
                $post->replies_count = $post->comments_count;
                return $post;
            });

        return Inertia::render('UserProfile', [
            'profileUser' => $user,
            'posts' => $posts,
            'postsCount' => $posts->count(),
            'isFollowing' => $authUser ? $authUser->isFollowing($user) : false,
            'auth' => [
                'user' => $authUser
            ],
            'likedPosts' => $likedPosts ?? []
        ]);
    }
}
