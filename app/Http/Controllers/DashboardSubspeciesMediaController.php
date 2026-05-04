<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Subspecies;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardSubspeciesMediaController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->is_admin, 403);

        $pending = Media::query()
            ->where('mediable_type', Subspecies::class)
            ->where('moderation_status', 'pending')
            ->with('mediable.parentSpecies')
            ->latest()
            ->paginate(24);

        return view('dashboard.subspecies.media', compact('pending'));
    }

    public function approve(Media $media): RedirectResponse
    {
        abort_unless(auth()->user()->is_admin, 403);
        abort_unless($media->mediable_type === Subspecies::class, 422);

        $media->update(['moderation_status' => 'approved']);

        return back()->with('success', 'Photo approved.');
    }

    public function reject(Media $media): RedirectResponse
    {
        abort_unless(auth()->user()->is_admin, 403);
        abort_unless($media->mediable_type === Subspecies::class, 422);

        $media->update(['moderation_status' => 'rejected']);

        return back()->with('success', 'Photo rejected.');
    }
}
