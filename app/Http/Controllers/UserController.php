<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use App\Models\Repost;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * Controller for handling user profile pages and all related
 * composite data such as posts, reposts, replies, and liked posts.
 *
 * NOTE:
 * - This controller is intentionally read-focused.
 * - All heavy business logic will be moved to a Service layer for maintainability.
 */

class UserController extends Controller
{   
    /**
     * API: Get minimal user list used for autocomplete / search.
     */
    public function apiIndex()
    {
        return User::select('id', 'name', 'avatar')->get();
    }

     /**
     * Show the public profile of a given username, including:
     * - User details
     * - Posts they created
     * - Posts they reposted
     * - Their replies
     * - Posts they liked
     *
     * This merges multiple content types into a unified feed.
     */
    public function show($username)
    {
        $profileUser = $this->getProfileUser($username);
        $authUser = Auth::user();
        $authUserId = $authUser?->id;

        // Fetch 3 content groups
        $posts   = $this->getUserPosts($profileUser->id, $authUserId);
        $reposts = $this->getUserReposts($profileUser->id, $authUserId);

        // Merge into a single timeline for Posts tab
        $allPosts = $this->mergeAllPosts($posts, $reposts);

        return Inertia::render('UserProfile', [
            'profileUser' => $profileUser,
            'posts'       => $allPosts,
            'postsCount'  => $allPosts->count(),
            'isFollowing' => $authUser ? $authUser->following->contains($profileUser->id) : false,
            'auth' => [ 'user' => $authUser ],
            'likedPosts' => $this->getLikedPosts($profileUser, $authUserId),
            'replies'    => $this->getUserReplies($profileUser, $authUserId),
            'reposts'    => $reposts, // Add this line - send reposts separately for Replies tab
        ]);
    }

     /**
     * Fetch profile owner with follower/following count.
     */ 
    private function getProfileUser($username)
    {
        return User::where('name', $username)
            ->withCount(['followers', 'following'])
            ->firstOrFail();
    }

    /**
     * Fetch user's posts with mapped metadata like likes, bookmarks, etc.
     */
    private function getUserPosts($userId, $authUserId)
    {
        return Post::with('user')
            ->withCount(['likes', 'bookmarks', 'reposts', 'comments'])
            ->where('user_id', $userId)
            ->latest()
            ->get()
            ->map(fn($post) => $this->mapPost($post, $authUserId));
    }

    /**
     * Fetch reposts made by the user and attach original post metadata.
     */
    private function getUserReposts($userId, $authUserId)
    {
        return Repost::with([
                'user',
                'post.user',
                'post' => fn($q) =>
                    $q->withCount(['likes', 'bookmarks', 'reposts'])
            ])
            ->where('user_id', $userId)
            ->latest()
            ->get()
            ->map(fn($repost) => $this->mapRepost($repost, $authUserId));
    }

    /**
     * Merge posts and reposts into a single feed sorted by creation time.
     */
    private function mergeAllPosts($posts, $reposts)
    {
        return collect($posts)->merge($reposts)
            ->sortByDesc(fn($i) => strtotime($i->created_at))
            ->values();
    }

    /**
     * Fetch posts liked by profile owner.
     */
    private function getLikedPosts($profileUser, $authUserId)
    {
        return $profileUser->likes()
            ->with(['user'])
            ->withCount(['likes', 'bookmarks', 'comments'])
            ->latest()
            ->get()
            ->map(fn($post) => $this->mapPost($post, $authUserId));
    }

    /**
     * Fetch profile owner's replies and include nested post/user context.
     */
    private function getUserReplies($profileUser, $authUserId)
    {
        return $profileUser->comments()
            ->with(['post.user', 'user'])
            ->latest()
            ->get()
            ->map(fn($c) => [
                'id'          => "comment_$c->id",
                'type'        => 'comment',
                'comment_id'  => $c->id,
                'content'     => $c->content,
                'created_at'  => $c->created_at,
                'user' => [
                    'id'    => $c->user->id,
                    'name'  => $c->user->name,
                    'email' => $c->user->email,
                ],
                'post' => [
                    'id'      => $c->post->id,
                    'content' => $c->post->content,
                    'user' => [
                        'id'    => $c->post->user->id,
                        'name'  => $c->post->user->name,
                        'email' => $c->post->user->email,
                    ],
                ],
                'likes_count'     => 0,
                'bookmarks_count' => 0,
                'reposts_count'   => 0,
                'replies_count'   => 0,
                'liked'           => false,
                'bookmarked'      => false,
                'reposted'        => false,
            ]);
    }

    /**
     * Map a Post model to include metadata for the frontend.
     */
    private function mapPost($post, $authUserId)
    {
        $post->type = 'post';
        $post->liked = $authUserId ? $post->likes()->where('user_id', $authUserId)->exists() : false;
        $post->bookmarked = $authUserId ? $post->bookmarks()->where('user_id', $authUserId)->exists() : false;
        $post->reposted = $authUserId ? $post->reposts()->where('user_id', $authUserId)->exists() : false;
        $post->replies_count = $post->comments_count ?? 0;

        return $post;
    }

    /**
     * Map a repost into a unified format similar to a post object.
     */
    private function mapRepost($repost, $authUserId)
    {
        $original = $repost->post;

        return (object) [
            'id'              => "repost_{$repost->id}",
            'type'            => 'repost',
            'repost_id'       => $repost->id,
            'user_id'         => $repost->user_id,
            'post_id'         => $original->id,
            'user' => [
                'id'    => $repost->user->id,
                'name'  => $repost->user->name,
                'email' => $repost->user->email,
            ],
            'content'         => $original->content,
            'images'          => $original->images,
            'repost_caption'  => $repost->caption,
            'repost_images'   => $repost->images,
            'created_at'      => $repost->created_at,
            'original_post_user' => [
                'id'    => $original->user->id,
                'name'  => $original->user->name,
                'email' => $original->user->email,
            ],
            'likes_count'     => $original->likes_count     ?? $original->likes()->count(),
            'bookmarks_count' => $original->bookmarks_count ?? $original->bookmarks()->count(),
            'reposts_count'   => $original->reposts_count   ?? $original->reposts()->count(),
            'replies_count'   => $original->comments()->count(),
            'liked'           => $authUserId ? $original->likes()->where('user_id', $authUserId)->exists() : false,
            'bookmarked'      => $authUserId ? $original->bookmarks()->where('user_id', $authUserId)->exists() : false,
            'reposted'        => $authUserId ? $original->reposts()->where('user_id', $authUserId)->exists() : false,
        ];
    }
}