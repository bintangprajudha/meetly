<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentController\StoreRequest;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Notification;
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

        // Buat notifikasi comment (jangan notif diri sendiri)
        if ($post->user_id !== $user->id) {
            Notification::create([
                'user_id' => $post->user_id,
                'actor_id' => $user->id,
                'type' => 'comment',
                'notifiable_id' => $post->id,
                'notifiable_type' => Post::class,
            ]);
        }

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