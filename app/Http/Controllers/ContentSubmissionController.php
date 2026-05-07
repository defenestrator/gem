<?php

namespace App\Http\Controllers;

use App\Models\ContentSubmission;
use App\Models\Species;
use App\Models\Subspecies;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContentSubmissionController extends Controller
{
    public function storeForSpecies(Request $request, Species $species): RedirectResponse
    {
        $this->authorize('create', ContentSubmission::class);

        $request->validate([
            'proposed_value' => ['required', 'string', 'min:50'],
        ]);

        $species->contentSubmissions()->create([
            'user_id'        => auth()->id(),
            'proposed_value' => $request->input('proposed_value'),
            'status'         => 'pending',
        ]);

        return back()->with('success', 'Description submitted for review. Thank you!');
    }

    public function storeForSubspecies(Request $request, Subspecies $subspecies): RedirectResponse
    {
        $this->authorize('create', ContentSubmission::class);

        $request->validate([
            'proposed_value' => ['required', 'string', 'min:50'],
        ]);

        $subspecies->contentSubmissions()->create([
            'user_id'        => auth()->id(),
            'proposed_value' => $request->input('proposed_value'),
            'status'         => 'pending',
        ]);

        return back()->with('success', 'Description submitted for review. Thank you!');
    }
}
