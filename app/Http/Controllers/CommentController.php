<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $data = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $user = $request->user();

        $comment = $post->comments()->create([
            'user_id' => $user->id,
            'content' => $data['content'],
        ]);

        // increment replies_count on post
        $post->increment('replies_count');

        $comment->load('user');

        // If the request is an Inertia visit, return a redirect (Inertia expects an Inertia response)
        if ($request->header('X-Inertia')) {
            // Redirect back so Inertia will follow and refresh page props
            return redirect()->back();
        }

        // For regular API/Fetch requests return JSON
        return response()->json(['comment' => $comment], 201);
    }

    /**
     * Return the latest comment for the given post (used by frontend to fetch the created comment).
     */
    public function latest(Post $post)
    {
        $comment = $post->comments()->with('user')->latest()->first();

        if (! $comment) {
            return response()->json(['comment' => null], 404);
        }

        return response()->json(['comment' => $comment], 200);
    }
}
