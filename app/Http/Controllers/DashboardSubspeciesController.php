<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Subspecies;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardSubspeciesController extends Controller
{
    public function edit(Subspecies $subspecies): View
    {
        abort_unless(auth()->user()->is_admin, 403);

        $subspecies->load('parentSpecies');
        $media = $subspecies->media()->orderBy('moderation_status')->latest()->get();

        return view('dashboard.subspecies.edit', compact('subspecies', 'media'));
    }

    public function update(Request $request, Subspecies $subspecies): RedirectResponse
    {
        abort_unless(auth()->user()->is_admin, 403);

        $validated = $request->validate([
            'genus'       => ['required', 'string', 'max:255'],
            'species'     => ['required', 'string', 'max:255'],
            'subspecies'  => ['required', 'string', 'max:255'],
            'author'      => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $subspecies->update($validated);

        return redirect()->route('subspecies.show', $subspecies)
            ->with('success', 'Subspecies updated.');
    }

    public function detachMedia(Subspecies $subspecies, Media $media): RedirectResponse
    {
        abort_unless(auth()->user()->is_admin, 403);
        abort_unless((int) $media->mediable_id === $subspecies->id && $media->mediable_type === Subspecies::class, 404);

        $media->forceDelete();

        return back()->with('success', 'Photo detached.');
    }
}
