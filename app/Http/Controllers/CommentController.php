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

        return response()->json(['comment' => $comment], 201);
    }
}
