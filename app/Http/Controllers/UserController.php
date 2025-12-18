<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Repost;
use Inertia\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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
        try {
            /** @var \App\Models\User|null $currentUser */
            $currentUser = Auth::user();
            
            // Make sure user is authenticated
            if (!$currentUser) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $users = User::select('id', 'name', 'email')
                ->where('id', '!=', $currentUser->id)
                ->orderBy('name', 'asc')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'avatar' => $user->email // Atau bisa: $user->avatar ?? '/default-avatar.png'
                    ];
                });

            return response()->json($users);
        } catch (\Exception $e) {
            Log::error('Failed to fetch users in apiIndex: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'message' => 'Failed to fetch users',
                'error' => app()->environment('local') ? $e->getMessage() : 'Server error'
            ], 500);
        }
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
            'auth' => ['user' => $authUser],
            'likedPosts' => $this->getLikedPosts($profileUser, $authUserId),
            'replies'    => $this->getUserReplies($profileUser, $authUserId),
            'reposts'    => $reposts, // Add this line - send reposts separately for Replies tab
        ]);
    }

    public function edit()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Anda harus login terlebih dahulu.');
        }

        return Inertia::render('components/EditProfile', [
            'user' => $user
        ]);
    }

    /**
     * Display followers of a user
     *
     * @param User $user
     * @return Response
     */
    public function followers(User $user): Response
    {
        /** @var User|null $authUser */
        $authUser = Auth::user();

        $followers = $user->followers()
            ->get()
            ->map(function (User $follower) use ($authUser): array {
                return [
                    'id' => $follower->id,
                    'name' => $follower->name,
                    'email' => $follower->email,
                    'avatar' => $follower->avatar,
                    'bio' => $follower->bio ?? null,
                    'followers_count' => $follower->followers()->count(),
                    'following_count' => $follower->following()->count(),
                    'is_following' => $authUser !== null && 
                        $authUser->following()
                            ->where('followed_user_id', $follower->id)
                            ->exists(),
                ];
            })
            ->toArray();

        return Inertia::render('FollowersFollowing', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'bio' => $user->bio ?? null,
                'followers_count' => $user->followers()->count(),
                'following_count' => $user->following()->count(),
            ],
            'followers' => $followers,
            'following' => [],
            'initialTab' => 'followers',
        ]);
    }

    /**
     * Display users that a user is following
     *
     * @param User $user
     * @return Response
     */
    public function following(User $user): Response
    {
        /** @var User|null $authUser */
        $authUser = Auth::user();

        $following = $user->following()
            ->get()
            ->map(function (User $followingUser) use ($authUser): array {
                return [
                    'id' => $followingUser->id,
                    'name' => $followingUser->name,
                    'email' => $followingUser->email,
                    'avatar' => $followingUser->avatar,
                    'bio' => $followingUser->bio ?? null,
                    'followers_count' => $followingUser->followers()->count(),
                    'following_count' => $followingUser->following()->count(),
                    'is_following' => $authUser !== null && 
                        $authUser->following()
                            ->where('followed_user_id', $followingUser->id)
                            ->exists(),
                ];
            })
            ->toArray();

        return Inertia::render('FollowersFollowing', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'bio' => $user->bio ?? null,
                'followers_count' => $user->followers()->count(),
                'following_count' => $user->following()->count(),
            ],
            'followers' => [],
            'following' => $following,
            'initialTab' => 'following',
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
            'videos'          => $original->videos,
            'media'           => $original->media,
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
