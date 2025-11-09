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
        $posts = Post::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Dashboard', [
            'user' => Auth::user(),
            'posts' => $posts,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:280',
            'image_url' => 'nullable|url',
        ]);

        $post = Post::create([
            'user_id' => Auth::id(),
            'content' => $request->content,
            'image_url' => $request->image_url,
        ]);

        Log::info('Post created successfully', [
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'content_length' => strlen($request->content),
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
