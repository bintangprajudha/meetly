<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    /**
     * User melaporkan post.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'post_id' => 'required|exists:posts,id',
            'reason' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        // Cek apakah user sudah report post ini sebelumnya
        $existingReport = Report::where('user_id', Auth::id())
            ->where('post_id', $validated['post_id'])
            ->first();

        if ($existingReport) {
            return redirect()->back()->with('error', 'You have already reported this post.');
        }

        $report = Report::create([
            'user_id' => Auth::id(),
            'post_id' => $validated['post_id'],
            'reason' => $validated['reason'],
            'description' => $validated['description'] ?? null,
            'status' => 'pending',
        ]);

        Log::info('Post reported', [
            'report_id' => $report->id,
            'post_id' => $validated['post_id'],
            'user_id' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Post reported successfully!');
    }

    /**
     * Admin update status report.
     */
    public function updateStatus(Request $request, Report $report)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'reviewed', 'resolved'])],
        ]);

        $report->update([
            'status' => $validated['status'],
        ]);

        Log::info('Report status updated', [
            'report_id' => $report->id,
            'new_status' => $validated['status'],
            'admin_id' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Report status updated!');
    }
}
