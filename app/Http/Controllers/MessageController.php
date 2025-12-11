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
    // komen
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
}
