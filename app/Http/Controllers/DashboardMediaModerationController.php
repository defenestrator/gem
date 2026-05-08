<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardMediaModerationController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->is_admin, 403);

        $pending = Media::query()
            ->where('moderation_status', 'pending')
            ->with('mediable')
            ->latest()
            ->paginate(24);

        return view('dashboard.media.index', compact('pending'));
    }

    public function approve(Media $media): RedirectResponse
    {
        abort_unless(auth()->user()->is_admin, 403);

        $media->update(['moderation_status' => 'approved']);

        return back()->with('success', 'Photo approved.');
    }

    public function reject(Media $media): RedirectResponse
    {
        abort_unless(auth()->user()->is_admin, 403);

        $media->update(['moderation_status' => 'rejected']);

        return back()->with('success', 'Photo rejected.');
    }

    public function setFeatured(Media $media): RedirectResponse
    {
        abort_unless(auth()->user()->is_admin, 403);

        Media::query()
            ->where('mediable_type', $media->mediable_type)
            ->where('mediable_id', $media->mediable_id)
            ->where('id', '!=', $media->id)
            ->where('is_featured', true)
            ->update(['is_featured' => false]);

        $media->update(['is_featured' => true]);

        return back()->with('success', 'Featured image updated.');
    }
}
