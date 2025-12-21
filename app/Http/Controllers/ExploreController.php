<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ExploreController extends Controller
{
    /**
     * Display the explore page with list of users
     */
    public function index(Request $request)
    {
        $authUser = $request->user();
        $search = $request->input('search', '');

        // Get all users excluding current user
        $usersQuery = User::withCount(['followers'])
            ->where('id', '!=', $authUser->id);

        // Apply search filter if provided
        if (!empty($search)) {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('username', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('bio', 'LIKE', "%{$search}%");
            });
        }

        // Get users with following status
        $users = $usersQuery
            ->orderBy('followers_count', 'desc')
            ->paginate(20)
            ->through(function ($user) use ($authUser) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username ?? strtolower(str_replace(' ', '', $user->name)),
                    'avatar' => $user->avatar,
                    'bio' => $user->bio ?? 'No bio yet',
                    'followers_count' => $user->followers_count,
                    // Gunakan method isFollowing yang sudah ada di Model
                    'isFollowing' => $authUser->isFollowing($user),
                ];
            });

        return Inertia::render('Explore', [
            'users' => $users,
            'search' => $search,
        ]);
    }
}