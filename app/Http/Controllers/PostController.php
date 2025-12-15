<?php

namespace App\Http\Controllers;

use App\Models\Post;
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

        $posts = Post::with('user')
            ->withCount('likes')
            ->withCount('bookmarks')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($post) use ($userId) {
                $post->liked = $userId ? $post->likes()->where('user_id', $userId)->exists() : false;
                $post->bookmarked = $userId ? $post->bookmarks()->where('user_id', $userId)->exists() : false;
                $post->likes_count = $post->likes_count ?? $post->likes()->count();
                $post->bookmarks_count = $post->bookmarks_count ?? $post->bookmarks()->count();
                return $post;
            });

        return Inertia::render('Dashboard', [
            'user' => Auth::user(),
            'posts' => $posts,
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

    public function store(Request $request)
    {
        // Validasi dasar
        $validated = $request->validate([
            'content' => 'required|string|max:280',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Max 2MB per image
            'images' => 'nullable|array|max:4',
            'videos.*' => 'nullable|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/x-ms-wmv|max:51200', // Max 20MB per video
            'videos' => 'nullable|array|max:4',
        ]);

        // Validasi total file maksimal 4
        $totalFiles = 0;
        if ($request->hasFile('images')) {
            $totalFiles += count($request->file('images'));
        }
        if ($request->hasFile('videos')) {
            $totalFiles += count($request->file('videos'));
        }

        if ($totalFiles > 4) {
            return redirect()->back()->withErrors([
                'media' => 'You can upload a maximum of 4 files (images and videos combined).'
            ])->withInput();
        }

        $imagePaths = [];
        $videoPaths = [];

        // Handle multiple image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('posts/images', 'public');
                $imagePaths[] = asset('storage/' . $path);
            }
        }

        // Handle video upload
        if ($request->hasFile('videos')) {
            foreach ($request->file('videos') as $video) {
                $path = $video->store('posts/videos', 'public');
                $videoPaths[] = asset('storage/' . $path);
            }
        }

        $post = Post::create([
            'user_id' => Auth::id(),
            'content' => $validated['content'],
            'images' => !empty($imagePaths) ? $imagePaths : null,
            'videos' => !empty($videoPaths) ? $videoPaths : null,
        ]);

        Log::info('Post created successfully', [
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'images_count' => count($imagePaths),
            'videos_count' => count($videoPaths),
        ]);

        return redirect()->back()->with('success', 'Post created successfully!');
    }

    public function show(Post $post)
    {
        return Inertia::render('PostDetail', [
            'post' => $post->load('user'),
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
            ->withCount('likes')
            ->withCount('bookmarks')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($post) use ($userId) {
                $post->liked = true; // Since we're on the likes page, these are liked
                $post->bookmarked = $post->bookmarks()->where('user_id', $userId)->exists();
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
            ->withCount('likes')
            ->withCount('bookmarks')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($post) use ($userId) {
                $post->liked = $post->likes()->where('user_id', $userId)->exists();
                $post->bookmarked = true; // Since we're on the bookmarks page, these are bookmarked
                return $post;
            });

        return Inertia::render('Bookmarks', [
            'user' => Auth::user(),
            'posts' => $posts,
        ]);
    }
}
