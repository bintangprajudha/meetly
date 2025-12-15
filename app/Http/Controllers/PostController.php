<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Repost;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Requests\PostController\StoreRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Class PostController
 *
 * Controller responsible for:
 * - Fetching main feed (posts + reposts)
 * - Storing posts
 * - Toggling likes & bookmarks
 * - Showing post detail
 * - Listing liked & bookmarked posts
 */
class PostController extends Controller
{
    /**
     * Display full feed (posts + reposts combined by date).
     */
    public function index()
    {
        $userId = Auth::id();

        $posts   = $this->getFormattedPosts($userId);
        $reposts = $this->getFormattedReposts($userId);

        $feed = $posts->merge($reposts)
            ->sortByDesc(fn ($item) => strtotime($item->created_at))
            ->values();

        return Inertia::render('Dashboard', [
            'user'  => Auth::user(),
            'posts' => $feed,
        ]);
    }

    /**
     * Toggle like for a post.
     */
    public function toggleLike(Post $post)
    {
        try {
            $userId = Auth::id();
            
            if (!$userId) {
                Log::warning('Unauthorized like attempt', ['post_id' => $post->id]);
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            Log::info('toggleLike triggered', [
                'user_id' => $userId,
                'post_id' => $post->id,
                'csrf_token' => request()->header('X-CSRF-TOKEN') ? 'present' : 'missing'
            ]);

            // Toggle like
            $exists = $post->likes()->where('user_id', $userId)->exists();
            
            if ($exists) {
                $post->likes()->detach($userId);
                $liked = false;
            } else {
                $post->likes()->attach($userId);
                $liked = true;
            }

            // Get fresh count
            $likesCount = $post->likes()->count();

            Log::info('toggleLike success', [
                'liked' => $liked,
                'likes_count' => $likesCount
            ]);

            return response()->json([
                'success' => true,
                'liked' => $liked,
                'likes_count' => $likesCount,
            ]);
            
        } catch (\Exception $e) {
            Log::error('toggleLike failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to toggle like',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle bookmark for a post.
     */
    public function toggleBookmark(Post $post)
    {
        try {
            $userId = Auth::id();
            
            if (!$userId) {
                Log::warning('Unauthorized bookmark attempt', ['post_id' => $post->id]);
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            Log::info('toggleBookmark triggered', [
                'user_id' => $userId,
                'post_id' => $post->id,
                'csrf_token' => request()->header('X-CSRF-TOKEN') ? 'present' : 'missing'
            ]);

            // Toggle bookmark
            $exists = $post->bookmarks()->where('user_id', $userId)->exists();
            
            if ($exists) {
                $post->bookmarks()->detach($userId);
                $bookmarked = false;
            } else {
                $post->bookmarks()->attach($userId);
                $bookmarked = true;
            }

            // Get fresh count
            $bookmarksCount = $post->bookmarks()->count();

            Log::info('toggleBookmark success', [
                'bookmarked' => $bookmarked,
                'bookmarks_count' => $bookmarksCount
            ]);

            return response()->json([
                'success' => true,
                'bookmarked' => $bookmarked,
                'bookmarks_count' => $bookmarksCount,
            ]);
            
        } catch (\Exception $e) {
            Log::error('toggleBookmark failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to toggle bookmark',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new post with optional multiple images.
     */
    public function store(StoreRequest $request)
    {
        $imagePaths = $this->handleImages($request);

        $post = Post::create([
            'user_id' => Auth::id(),
            'content' => $request->content,
            'images'  => $imagePaths ?: null,
        ]);

        Log::info('Post created', [
            'post_id'       => $post->id,
            'user_id'       => Auth::id(),
            'content_len'   => strlen($request->content ?? ''),
            'images_count'  => count($imagePaths),
        ]);

        return redirect()->back()->with('success', 'Post created successfully!');
    }

    /**
     * Show post detail including comments.
     */
    public function show(Post $post)
    {
        $userId = Auth::id();
        $post->loadCount(['comments', 'likes', 'bookmarks', 'reposts']);
        $post->load([
            'user',
            'comments' => fn ($q) => $q->with('user')->latest(),
        ]);

        $post->liked = $userId ? $post->likes()->where('user_id', $userId)->exists() : false;
        $post->bookmarked = $userId ? $post->bookmarks()->where('user_id', $userId)->exists() : false;
        $post->reposted = $userId ? $post->reposts()->where('user_id', $userId)->exists() : false;
        $post->replies_count = $post->comments_count;

        return Inertia::render('PostDetail', [
            'post' => $post,
            'user' => Auth::user(),
        ]);
    }

    /**
     * Delete a post owned by the authenticated user.
     */
    public function destroy(Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            throw ValidationException::withMessages([
                'authorization' => 'You can only delete your own posts.',
            ]);
        }

        $post->delete();

        Log::info('Post deleted', [
            'post_id' => $post->id,
            'user_id' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Post deleted successfully!');
    }

    /**
     * List posts liked by authenticated user.
     */
    public function likes()
    {
        $userId = Auth::id();

        $posts = Post::whereHas('likes', fn ($q) => $q->where('user_id', $userId))
            ->with('user')
            ->withCount(['likes', 'bookmarks', 'comments'])
            ->latest()
            ->get()
            ->map(function ($post) use ($userId) {
                $post->liked        = true;
                $post->bookmarked   = $post->bookmarks()->where('user_id', $userId)->exists();
                $post->replies_count = $post->comments_count;
                return $post;
            });

        return Inertia::render('Likes', [
            'user'  => Auth::user(),
            'posts' => $posts,
        ]);
    }

    /**
     * List posts bookmarked by authenticated user.
     */
    public function bookmarks()
    {
        $userId = Auth::id();

        $posts = Post::whereHas('bookmarks', fn ($q) => $q->where('user_id', $userId))
            ->with('user')
            ->withCount(['likes', 'bookmarks', 'comments'])
            ->latest()
            ->get()
            ->map(function ($post) use ($userId) {
                $post->liked        = $post->likes()->where('user_id', $userId)->exists();
                $post->bookmarked   = true;
                $post->replies_count = $post->comments_count;
                return $post;
            });

        return Inertia::render('Bookmarks', [
            'user'  => Auth::user(),
            'posts' => $posts,
        ]);
    }

    /* -------------------------------------------------------
     | Private Helper Methods
     ------------------------------------------------------- */

    /**
     * Handle multiple image uploads.
     */
    private function handleImages(Request $request)
    {
        if (! $request->hasFile('images')) {
            return [];
        }

        return collect($request->file('images'))
            ->map(fn ($img) => asset('storage/' . $img->store('posts', 'public')))
            ->toArray();
    }

    /**
     * Format original posts for feed.
     */
    private function getFormattedPosts($userId)
    {
        return Post::with('user')
            ->withCount(['likes', 'bookmarks', 'reposts'])
            ->latest()
            ->get()
            ->map(function ($post) use ($userId) {
                $post->type            = 'post';
                $post->liked           = $post->likes()->where('user_id', $userId)->exists();
                $post->bookmarked      = $post->bookmarks()->where('user_id', $userId)->exists();
                $post->reposted        = $post->reposts()->where('user_id', $userId)->exists();
                $post->likes_count     = $post->likes_count;
                $post->bookmarks_count = $post->bookmarks_count;
                $post->reposts_count   = $post->reposts_count;
                return $post;
            });
    }

    /**
     * Convert reposts into unified feed objects.
     */
    private function getFormattedReposts($userId)
    {
        return Repost::with(['user', 'post' => fn ($q) =>
            $q->with('user')->withCount(['likes', 'bookmarks', 'reposts'])
        ])
        ->latest()
        ->get()
        ->map(function ($repost) use ($userId) {
            $post = $repost->post;

            return (object) [
                'id'                => "repost_{$repost->id}",
                'type'              => 'repost',
                'repost_id'         => $repost->id,
                'user_id'           => $repost->user_id,
                'post_id'           => $post->id,
                'user'              => $repost->user,
                'content'           => $post->content,
                'images'            => $post->images,
                'repost_caption'    => $repost->caption,
                'repost_images'     => $repost->images,
                'created_at'        => $repost->created_at,
                'original_post_user'=> $post->user,
                'likes_count'       => $post->likes_count,
                'bookmarks_count'   => $post->bookmarks_count,
                'reposts_count'     => $post->reposts_count,
                'replies_count'     => $post->comments()->count(),
                'liked'             => $post->likes()->where('user_id', $userId)->exists(),
                'bookmarked'        => $post->bookmarks()->where('user_id', $userId)->exists(),
                'reposted'          => $post->reposts()->where('user_id', $userId)->exists(),
            ];
        });
    }
}