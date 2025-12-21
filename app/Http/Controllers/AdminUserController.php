<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Report;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display admin dashboard with reports.
     */
    public function dashboard()
    {
        $reports = Report::with(['user', 'post.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Dashboard', [
            'reports' => $reports,
        ]);
    }

    /**
     * Delete a user (admin only).
     */
    public function destroy(User $user)
    {
        try {
            // Gunakan policy untuk cek otorisasi
            $this->authorize('delete', $user);

            $userName = $user->name;
            $userId = $user->id;

            $user->delete();

            Log::info('User deleted by admin', [
                'deleted_user_id' => $userId,
                'deleted_user_name' => $userName,
                'admin_id' => Auth::id(),
            ]);

            return redirect()->back()->with('success', 'User deleted successfully!');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized user deletion attempt', [
                'target_user_id' => $user->id,
                'admin_id' => Auth::id(),
            ]);

            return redirect()->back()->with('error', 'You are not authorized to delete this user.');
        } catch (\Exception $e) {
            Log::error('Error deleting user', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'admin_id' => Auth::id(),
            ]);

            return redirect()->back()->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }
}
