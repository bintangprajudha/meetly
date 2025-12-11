<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Message;
use App\Events\NewMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class MessageController extends Controller
{   
    // raka
    public function index(User $user)
    {
        return Inertia::render('Messages/Chat', [
            'chatUser' => $user,
            'auth' => [ 
                'user' => Auth::user(), 
            ],
        ]);
    }

    public function fetch(User $user)
    {
        $me = Auth::id();

        return Message::where(function ($q) use ($me, $user) {
                $q->where('sender_id', $me)
                  ->where('receiver_id', $user->id);
            })
            ->orWhere(function ($q) use ($me, $user) {
                $q->where('sender_id', $user->id)
                  ->where('receiver_id', $me);
            })
            ->orderBy('created_at')
            ->get();
    }

    public function send(Request $request)
    {
        $message = Message::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message'     => $request->message,
        ]);

        broadcast(new NewMessage($message))->toOthers(); // realtime

        return $message;
    }

    public function list()
    {
        $me = Auth::id();

        $users = User::where('id', '!=', $me)->get();

        $result = $users->map(function ($user) use ($me) {
            $lastMessage = Message::where(function ($q) use ($me, $user) {
                    $q->where('sender_id', $me)->where('receiver_id', $user->id);
                })
                ->orWhere(function ($q) use ($me, $user) {
                    $q->where('sender_id', $user->id)->where('receiver_id', $me);
                })
                ->orderBy('id', 'DESC')
                ->first();

            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar ?? null,
                ],
                'last_message' => $lastMessage->message ?? null,
                'last_message_time' => $lastMessage->created_at ?? null,
            ];
        });

        return $result;
    }

    public function sharePost(Request $request)
    {
        try {
            $request->validate([
                'post_id' => 'required|exists:posts,id',
                'user_ids' => 'required|array|min:1',
                'user_ids.*' => 'exists:users,id',
            ]);

            $post = \App\Models\Post::findOrFail($request->post_id);
            $senderId = Auth::id();

        $messages = [];
        $sharedCount = 0;

        foreach ($request->user_ids as $userId) {
            // Skip if trying to share with self
            if ($userId == $senderId) continue;

            // Build message content with post details
            $messageContent = "Shared a post: \"{$post->content}\"";
            if ($post->images && count($post->images) > 0) {
                $messageContent .= "\n\n[Images: " . count($post->images) . " attached]";
            }

            $message = Message::create([
                'sender_id' => $senderId,
                'receiver_id' => $userId,
                'message' => $messageContent,
                'images' => $post->images, // Include images from the post
            ]);

            $messages[] = $message;
            $sharedCount++;

            // Broadcast the message
            broadcast(new NewMessage($message))->toOthers();
        }

        $message = $sharedCount > 0
            ? "Post shared successfully with {$sharedCount} user" . ($sharedCount > 1 ? 's' : '') . "!"
            : "No posts were shared.";

            return response()->json([
                'success' => $sharedCount > 0,
                'message' => $message,
                'shared_count' => $sharedCount,
                'messages' => $messages,
            ]);
        } catch (\Exception $e) {
            \Log::error('Share post failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to share post: ' . $e->getMessage(),
            ], 500);
        }
    }
}
