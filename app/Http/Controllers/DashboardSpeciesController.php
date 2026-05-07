<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSpeciesRequest;
use App\Models\Media;
use App\Models\Species;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardSpeciesController extends Controller
{
    public function edit(Species $species): View
    {
        abort_unless(auth()->user()->is_admin, 403);

        $media = $species->media()->orderBy('moderation_status')->latest()->get();

        return view('dashboard.species.edit', compact('species', 'media'));
    }

    public function update(UpdateSpeciesRequest $request, Species $species): RedirectResponse
    {
        abort_unless(auth()->user()->is_admin, 403);

        $species->update($request->validated());

        return redirect()->route('species.show', $species)
            ->with('success', 'Species updated.');
    }

    public function detachMedia(Species $species, Media $media): RedirectResponse
    {
        abort_unless(auth()->user()->is_admin, 403);
        abort_unless((int) $media->mediable_id === $species->id && $media->mediable_type === Species::class, 404);

        $media->forceDelete();

        return back()->with('success', 'Photo detached.');
    }
}
