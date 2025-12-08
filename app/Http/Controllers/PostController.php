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
        // Validate and use the validated data array to avoid accessing missing properties
        $validated = $request->validate([
            'content' => 'required|string|max:280',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // max 2MB
        ]);
    
        $imagePath = null;
        
        // Handle image upload if present
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
        }
    
        $post = Post::create([
            'user_id' => Auth::id(),
            'content' => $validated['content'],
            'image_url' => $imagePath ? asset('storage/' . $imagePath) : null,
        ]);
    
        Log::info('Post created successfully', [
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'content_length' => strlen((string) ($validated['content'] ?? '')),
            'has_image' => !is_null($imagePath),
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
}
