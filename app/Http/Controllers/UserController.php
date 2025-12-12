<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use App\Models\Repost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserController extends Controller
{
    public function apiIndex()
    {
        return User::select('id', 'name', 'avatar')->get();
    }

    public function show($username)
    {
        // Find user by username
        $user = User::where('name', $username)
            ->withCount(['followers', 'following']) 
            ->firstOrFail();

        $userId = Auth::id();

        // Get user's posts with eager loading
        $posts = Post::with('user')
            ->where('user_id', $user->id)
            ->withCount('likes')
            ->withCount('bookmarks')
            ->withCount('reposts')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($post) use ($userId) {
                $post->liked = $userId ? $post->likes()->where('user_id', $userId)->exists() : false;
                $post->bookmarked = $userId ? $post->bookmarks()->where('user_id', $userId)->exists() : false;
                $post->reposted = $userId ? $post->reposts()->where('user_id', $userId)->exists() : false;
                $post->likes_count = $post->likes_count ?? $post->likes()->count();
                $post->bookmarks_count = $post->bookmarks_count ?? $post->bookmarks()->count();
                $post->reposts_count = $post->reposts_count ?? $post->reposts()->count();
                $post->type = 'post';
                return $post;
            });

        // Get user's reposts
        $reposts = Repost::with(['user', 'post' => function ($query) {
            $query->with('user')->withCount(['likes', 'bookmarks', 'reposts']);
        }])
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($repost) use ($userId) {
            $originalPost = $repost->post;
            
            return (object) [
                'id' => 'repost_' . $repost->id,
                'type' => 'repost',
                'repost_id' => $repost->id,
                'user_id' => $repost->user_id,
                'post_id' => $originalPost->id,
                'user' => [
                    'id' => $repost->user->id,
                    'name' => $repost->user->name,
                    'email' => $repost->user->email,
                ],
                'content' => $originalPost->content,
                'images' => $originalPost->images,
                'repost_caption' => $repost->caption,
                'repost_images' => $repost->images,
                'created_at' => $repost->created_at,
                'original_post_user' => [
                    'id' => $originalPost->user->id,
                    'name' => $originalPost->user->name,
                    'email' => $originalPost->user->email,
                ],
                'likes_count' => $originalPost->likes_count ?? $originalPost->likes()->count(),
                'bookmarks_count' => $originalPost->bookmarks_count ?? $originalPost->bookmarks()->count(),
                'reposts_count' => $originalPost->reposts_count ?? $originalPost->reposts()->count(),
                'replies_count' => $originalPost->comments()->count(),
                'liked' => $userId ? $originalPost->likes()->where('user_id', $userId)->exists() : false,
                'bookmarked' => $userId ? $originalPost->bookmarks()->where('user_id', $userId)->exists() : false,
                'reposted' => $userId ? $originalPost->reposts()->where('user_id', $userId)->exists() : false,
            ];
        });

        // Merge posts and reposts
        $allPosts = collect($posts)->merge($reposts)
            ->sortByDesc(function ($item) {
                return strtotime($item->created_at);
            })
            ->values();

        $likedPosts = $user->likes()->with('user')->latest()->get();
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

        // Get user's comments/replies on other posts
        $replies = $user->comments()
            ->with(['post.user', 'user'])
            ->latest()
            ->get()
            ->map(function ($comment) {
                return [
                    'id' => 'comment_' . $comment->id,
                    'type' => 'comment',
                    'comment_id' => $comment->id,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at,
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                        'email' => $comment->user->email,
                    ],
                    'post' => [
                        'id' => $comment->post->id,
                        'content' => $comment->post->content,
                        'user' => [
                            'id' => $comment->post->user->id,
                            'name' => $comment->post->user->name,
                            'email' => $comment->post->user->email,
                        ],
                    ],
                    'likes_count' => 0,
                    'bookmarks_count' => 0,
                    'reposts_count' => 0,
                    'replies_count' => 0,
                ];
            });

        return Inertia::render('UserProfile', [
            'profileUser' => $user,
            'posts' => $allPosts,
            'postsCount' => $allPosts->count(),
            'isFollowing' => auth()->check()
                ? auth()->user()->isFollowing($user)
                : false,
            'auth' => [
                'user' => $authUser
            ],
            'likedPosts' => $likedPosts ?? [],
            'replies' => $replies ?? []
        ]);
    }
}
