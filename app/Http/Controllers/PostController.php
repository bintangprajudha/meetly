<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostControllerStoreRequest;
use App\Models\Post;
use App\Models\Repost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PostController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // Get all posts
        $posts = Post::with('user')
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

        // Get all reposts and transform them into post-like objects for the feed
        $reposts = Repost::with(['user', 'post' => function ($query) {
            $query->with('user')->withCount(['likes', 'bookmarks', 'reposts']);
        }])
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

        // Merge posts and reposts, then sort by created_at descending
        $allFeed = collect($posts)->merge($reposts)
            ->sortByDesc(function ($item) {
                return strtotime($item->created_at);
            })
            ->values();

        return Inertia::render('Dashboard', [
            'user' => Auth::user(),
            'posts' => $allFeed,
        ]);
    }

    // Toggle like for current user — returns JSON with updated counts and status
    public function toggleLike(Post $post)
    {
        $userId = Auth::id();
        if (! $userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        Log::info('toggleLike called', ['post_id' => $post->id, 'user_id' => $userId]);

        $exists = $post->likes()->where('user_id', $userId)->exists();

        if ($exists) {
            $post->likes()->detach($userId);
            $liked = false;
        } else {
            $post->likes()->attach($userId);
            $liked = true;
        }

        $likesCount = $post->likes()->count();
        $post->likes_count = $likesCount;
        $post->saveQuietly();

        return response()->json([
            'liked' => $liked,
            'likes_count' => $likesCount,
        ]);
    }

    // Toggle bookmark for current user — returns JSON with updated status
    public function toggleBookmark(Post $post)
    {
        $userId = Auth::id();
        if (! $userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        Log::info('toggleBookmark called', ['post_id' => $post->id, 'user_id' => $userId]);

        $exists = $post->bookmarks()->where('user_id', $userId)->exists();

        if ($exists) {
            $post->bookmarks()->detach($userId);
            $bookmarked = false;
        } else {
            $post->bookmarks()->attach($userId);
            $bookmarked = true;
        }

        $bookmarksCount = $post->bookmarks()->count();

        return response()->json([
            'bookmarked' => $bookmarked,
            'bookmarks_count' => $bookmarksCount,
        ]);
    }

    public function store(PostControllerStoreRequest $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:280',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // Multiple images
            'images' => 'nullable|array|max:4', // Max 4 images
        ]);

        $imagePaths = [];

        // Handle multiple image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('posts', 'public');
                $imagePaths[] = asset('storage/' . $path);
            }
        }

        $post = Post::create([
            'user_id' => Auth::id(),
            'content' => $request->content,
            'images' => !empty($imagePaths) ? $imagePaths : null,
        ]);

        Log::info('Post created successfully', [
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'content_length' => strlen((string) ($request->content ?? '')),
            'images_count' => count($imagePaths),
        ]);

        return redirect()->back()->with('success', 'Post created successfully!');
    }

    public function show(Post $post)
    {
        $post->loadCount('comments');
        // Load the post user and comments (with their user), ordering comments newest-first
        $post->load([
            'user',
            'comments' => function ($query) {
                $query->with('user')->orderBy('created_at', 'desc');
            },
        ]);
        
        return Inertia::render('PostDetail', [
            'post' => $post,
            'user' => Auth::user(),
        ]);
    }

    public function destroy(Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            throw ValidationException::withMessages([
                'authorization' => 'You can only delete your own posts.',
            ]);
        }

        $post->delete();

        Log::info('Post deleted successfully', [
            'post_id' => $post->id,
            'user_id' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Post deleted successfully!');
    }

    public function likes()
    {
        $userId = Auth::id();

        $posts = Post::whereHas('likes', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->with('user')
            ->withCount(['likes', 'bookmarks', 'comments'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($post) use ($userId) {
                $post->liked = true; // Since we're on the likes page, these are liked
                $post->bookmarked = $post->bookmarks()->where('user_id', $userId)->exists();
                $post->replies_count = $post->comments_count;
                return $post;
            });

        return Inertia::render('Likes', [
            'user' => Auth::user(),
            'posts' => $posts,
        ]);
    }

    public function bookmarks()
    {
        $userId = Auth::id();

        $posts = Post::whereHas('bookmarks', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->with('user')
            ->withCount(['likes', 'bookmarks', 'comments'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($post) use ($userId) {
                $post->liked = $post->likes()->where('user_id', $userId)->exists();
                $post->bookmarked = true; // Since we're on the bookmarks page, these are bookmarked
                $post->replies_count = $post->comments_count;
                return $post;
            });

        return Inertia::render('Bookmarks', [
            'user' => Auth::user(),
            'posts' => $posts,
        ]);
    }
}
