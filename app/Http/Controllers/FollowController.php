<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller; // 🔥 WAJIB! Ini yang hilang
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FollowController extends Controller
{
   

    public function store(User $user)
    {
        $me = Auth::user();

        if ($me->id === $user->id) {
            return redirect()->back()->with('error', 'Tidak bisa follow diri sendiri.');
        }

        if ($me->isFollowing($user)) {
            return redirect()->back()->with('info', 'Anda sudah mengikuti user ini.');
        }

        $me->follow($user);

        return redirect()->back()->with('success', 'Berhasil mengikuti ' . $user->name);
    }

    public function destroy(User $user)
    {
        $me = Auth::user();

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
