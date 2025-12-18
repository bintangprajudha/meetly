<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Notification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    public function store(User $user)
    {
        /** @var \App\Models\User|null $me */
        $me = Auth::user();

        if (!$me) {
            return redirect()->back()->with('error', 'Anda harus login terlebih dahulu.');
        }

        if ($me->id === $user->id) {
            return redirect()->back()->with('error', 'Tidak bisa follow diri sendiri.');
        }

        if ($me->isFollowing($user)) {
            return redirect()->back()->with('info', 'Anda sudah mengikuti user ini.');
        }

        $me->follow($user);

        Notification::create([
            'user_id' => $user->id, // penerima notifikasi
            'actor_id' => $me->id, // yang follow
            'type' => 'follow',
            'data' => [
                'message' => $me->name . ' mulai mengikuti Anda'
            ]
        ]);

        return redirect()->back()->with('success', 'Berhasil mengikuti ' . $user->name);
    }

    public function destroy(User $user)
    {
        /** @var \App\Models\User|null $me */
        $me = Auth::user();

        if (!$me) {
            return redirect()->back()->with('error', 'Anda harus login terlebih dahulu.');
        }

        if (!$me->isFollowing($user)) {
            return redirect()->back()->with('info', 'Anda belum mengikuti user ini.');
        }

        $me->unfollow($user);

        return redirect()->back()->with('success', 'Berhenti mengikuti ' . $user->name);
    }

    public function followers(User $user)
    {
        return Inertia::render('Follow/Followers', [
            'user' => $user,
            'followers' => $user->followers()->paginate(20)
        ]);
    }

    public function following(User $user)
    {
        return Inertia::render('Follow/Following', [
            'user' => $user,
            'following' => $user->following()->paginate(20)
        ]);
    }
}
