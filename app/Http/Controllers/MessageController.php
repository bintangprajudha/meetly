<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Message;
use App\Events\NewMessage;
use App\Events\MessageRead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class MessageController extends Controller
{
    public function index(User $user = null)
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

        if ($user->id === $me) {
            return response()->json(['error' => 'Cannot chat with yourself'], 403);
        }

        $messages = Message::where(function ($q) use ($me, $user) {
            $q->where('sender_id', $me)
                ->where('receiver_id', $user->id);
        })
            ->orWhere(function ($q) use ($me, $user) {
                $q->where('sender_id', $user->id)
                    ->where('receiver_id', $me);
            })
            ->orderBy('created_at')
            ->get();

        Message::where('sender_id', $user->id)
            ->where('receiver_id', $me)
            ->where('status', '!=', 'read')
            ->update(['status' => 'read']);

        return $messages;
    }

    public function send(Request $request)
    {
        $me = Auth::id();

        if ($request->receiver_id == $me) {
            return response()->json(['error' => 'Cannot send message to yourself'], 403);
        }
        $message = Message::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message'     => $request->message,
            'status'      => 'sent',
        ]);

        broadcast(new NewMessage($message))->toOthers(); // realtime
        event(new NewMessage($message));

        return $message;
    }

    public function list()
    {
        $me = Auth::id();

        $conversationPartners = Message::where(function ($query) use ($me) {
            $query->where('sender_id', $me)
                ->orWhere('receiver_id', $me);
        })
            ->select('sender_id', 'receiver_id')
            ->get()
            ->flatMap(function ($message) use ($me) {
                return [$message->sender_id, $message->receiver_id];
            })
            ->unique()
            ->reject(function ($id) use ($me) {
                return $id == $me;
            })
            ->values();

        $users = User::whereIn('id', $conversationPartners)->get();

        $result = $users->map(function ($user) use ($me) {
            $lastMessage = Message::where(function ($q) use ($me, $user) {
                $q->where('sender_id', $me)->where('receiver_id', $user->id);
            })
                ->orWhere(function ($q) use ($me, $user) {
                    $q->where('sender_id', $user->id)->where('receiver_id', $me);
                })
                ->orderBy('id', 'DESC')
                ->first();

            if (!$lastMessage) {
                return null;
            }

            $unreadCount = Message::where('sender_id', $user->id)
                ->where('receiver_id', $me)
                ->where('status', '!=', 'read')
                ->count();

            $isRead = ($lastMessage->sender_id === $me && $lastMessage->status === 'read');

            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => null,
                ],
                'last_message' => $lastMessage->message ?? null,
                'last_message_at' => $lastMessage->created_at ?? null,
                'is_read' => $isRead,
                'unread_count' => $unreadCount,
            ];
        })->filter()->values();

        return $result;
    }

    public function users()
    {
        $me = Auth::id();

        $users = User::where('id', '!=', $me)
            ->select('id', 'name', 'email',)
            ->orderBy('name')
            ->get();

        return $users;
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120'
        ]);

        $path = $request->file('image')->store('messages', 'public');
        $url = url("/storage/{$path}");

        return $url;
    }

    public function markAsRead(Request $request, User $user)
    {
        $me = Auth::id();

        // update pesan menjadi read
        $updated = Message::where('sender_id', $user->id)
            ->where('receiver_id', $me)
            ->where('status', '!=', 'read')
            ->update(['status' => 'read']);

        // ambil pesan yang sudah read untuk broadcast
        $messages = Message::where('sender_id', $user->id)
            ->where('receiver_id', $me)
            ->where('status', 'read')
            ->get();

        foreach ($messages as $msg) {
            broadcast(new MessageRead($msg->id, $msg->sender_id))->toOthers();
            event(new MessageRead($msg->id, $msg->sender_id));
        }

        return $updated;
    }


    public function destroy(Message $message)
    {
        if ($message->sender_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // hard delete
        $message->delete();

        return true; // simple, clean, sama seperti send()
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
                if ($post->videos && count($post->videos) > 0) {
                    $messageContent .= "\n[Videos: " . count($post->videos) . " attached]";
                }

                $message = Message::create([
                    'sender_id' => $senderId,
                    'receiver_id' => $userId,
                    'message' => $messageContent,
                    'images' => $post->images, // Include images from the post
                    'videos' => $post->videos, // Include videos from the post
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
            Log::error('Share post failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to share post: ' . $e->getMessage(),
            ], 500);
        }
    }
}
