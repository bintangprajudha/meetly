<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Repost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RepostController extends Controller
{
    /**
     * Store a newly created repost in storage.
     * 
     * Repost akan muncul sebagai post baru di feed dengan konten original post
     * dan caption tambahan user (jika ada)
     */
    public function store(Request $request, Post $post)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'caption' => 'nullable|string|max:280',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'images' => 'nullable|array|max:4',
        ]);

        // Handle image uploads
        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('reposts', 'public');
                $images[] = Storage::url($path);
            }
        }

        // Create repost record
        $repost = Repost::create([
            'user_id' => $userId,
            'post_id' => $post->id,
            'caption' => $validated['caption'] ?? null,
            'images' => !empty($images) ? $images : null,
        ]);

        // Update reposts_count on original post
        $post->increment('reposts_count');

        // If request expects JSON (AJAX), return JSON; otherwise redirect back for Inertia
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Repost created successfully',
                'repost' => $repost,
                'reposts_count' => $post->reposts()->count(),
            ]);
        }

        return redirect()->back();
    }

    /**
     * Delete a repost.
     */
    public function destroy(Request $request, Repost $repost)
    {
        $userId = Auth::id();
        if (!$userId || $repost->user_id !== $userId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post = $repost->post;
        $repost->delete();

        // Update reposts_count on original post
        $post->decrement('reposts_count');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Repost deleted successfully',
                'reposts_count' => $post->reposts()->count(),
            ]);
        }

        return redirect()->back();
    }

    /**
     * Get reposts for a specific post with user information
     * These represent the repost as separate "posts" in feed
     */
    public function getReposts(Post $post)
    {
        $userId = Auth::id();

        $reposts = $post->reposts()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($repost) use ($userId) {
                return [
                    'id' => 'repost_' . $repost->id,
                    'type' => 'repost',
                    'repost_id' => $repost->id,
                    'user_id' => $repost->user_id,
                    'user' => $repost->user,
                    'content' => $repost->post->content, // original post content
                    'images' => $repost->post->images, // original post images
                    'repost_caption' => $repost->caption, // repost caption (additional)
                    'repost_images' => $repost->images, // repost images (additional)
                    'created_at' => $repost->created_at,
                    'original_post' => $repost->post,
                    'likes_count' => 0,
                    'bookmarks_count' => 0,
                    'reposts_count' => 0,
                    'replies_count' => 0,
                    'liked' => false,
                    'bookmarked' => false,
                    'reposted' => false,
                ];
            });

        return response()->json($reposts);
    }

    /**
     * Check if current user has reposted a post
     */
    public function hasUserReposted(Post $post)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['reposted' => false]);
        }

        $reposted = $post->reposts()->where('user_id', $userId)->exists();

        return response()->json(['reposted' => $reposted]);
    }

    /**
     * Get user's reposts as posts for profile/timeline
     */
    public function getUserReposts($userId)
    {
        $currentUserId = Auth::id();

        // Get all reposts by a user with their original posts
        $reposts = Repost::where('user_id', $userId)
            ->with(['user', 'post' => function ($query) {
                $query->with('user');
            }])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($repost) use ($currentUserId) {
                return [
                    'id' => 'repost_' . $repost->id,
                    'type' => 'repost',
                    'repost_id' => $repost->id,
                    'user_id' => $repost->user_id,
                    'user' => [
                        'id' => $repost->user->id,
                        'name' => $repost->user->name,
                        'email' => $repost->user->email,
                    ],
                    'content' => $repost->post->content,
                    'images' => $repost->post->images,
                    'repost_caption' => $repost->caption,
                    'repost_images' => $repost->images,
                    'created_at' => $repost->created_at,
                    'original_post' => [
                        'id' => $repost->post->id,
                        'user' => [
                            'id' => $repost->post->user->id,
                            'name' => $repost->post->user->name,
                            'email' => $repost->post->user->email,
                        ],
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

        return response()->json($reposts);
    }
}
