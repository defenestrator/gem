<?php

namespace App\Http\Controllers;

use App\Models\ContentSubmission;
use App\Models\Species;
use App\Models\Subspecies;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardContentSubmissionController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->is_admin, 403);

        $pending = ContentSubmission::query()
            ->where('status', 'pending')
            ->with(['submittable', 'user'])
            ->latest()
            ->paginate(20);

        return view('dashboard.submissions.index', compact('pending'));
    }

    public function approve(ContentSubmission $submission): RedirectResponse
    {
        abort_unless(auth()->user()->is_admin, 403);

        $submission->submittable->update(['description' => $submission->proposed_value]);

        $submission->update([
            'status'      => 'approved',
            'reviewer_id' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Submission approved and description updated.');
    }

    public function reject(ContentSubmission $submission): RedirectResponse
    {
        abort_unless(auth()->user()->is_admin, 403);

        $submission->update([
            'status'      => 'rejected',
            'reviewer_id' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Submission rejected.');
    }
}
