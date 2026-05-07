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

        $submittable = $submission->submittable;
        $revisions   = $submittable->description_revisions ?? [];

        $revisions[] = [
            'value'              => $submission->proposed_value,
            'submitted_by_id'    => $submission->user_id,
            'submitted_by_name'  => $submission->user->name,
            'approved_by_id'     => auth()->id(),
            'approved_by_name'   => auth()->user()->name,
            'submission_id'      => $submission->id,
            'approved_at'        => now()->toISOString(),
        ];

        $submittable->update([
            'description'           => $submission->proposed_value,
            'description_revisions' => $revisions,
        ]);

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
