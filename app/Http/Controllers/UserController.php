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
            $query->with('user');
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
                'likes_count' => 0,
                'bookmarks_count' => 0,
                'reposts_count' => 0,
                'replies_count' => 0,
                'liked' => false,
                'bookmarked' => false,
                'reposted' => false,
            ];
        });

        // Merge posts and reposts
        $allPosts = collect($posts)->merge($reposts)
            ->sortByDesc(function ($item) {
                return strtotime($item->created_at);
            })
            ->values();

        $likedPosts = $user->likes()->with('user')->latest()->get();

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
                'user' => Auth::user()
            ],
            'likedPosts' => $likedPosts ?? [],
            'replies' => $replies ?? []
        ]);
    }
}
