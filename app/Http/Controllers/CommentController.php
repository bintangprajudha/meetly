<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentController\StoreRequest;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(StoreRequest $request, Post $post)
    {

        $user = $request->user();

        $comment = $post->comments()->create([
            'user_id' => $user->id,
            'content' => $request->content,
        ]);

        $comment->load('user');

        if ($request->header('X-Inertia')) {
            return redirect()->back();
        }

        // For regular API/Fetch requests return JSON
        return response()->json(['comment' => $comment], 201);
    }

    public function latest(Post $post)
    {
        $comment = $post->comments()->with('user')->latest()->first();

        if (! $comment) {
            return response()->json(['comment' => null], 404);
        }

        return response()->json(['comment' => $comment], 200);
    }
}
