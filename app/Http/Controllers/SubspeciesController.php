<?php

namespace App\Http\Controllers;

use App\Models\Subspecies;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SubspeciesController extends Controller
{
    public function show(Subspecies $subspecies): View
    {
        $isAdmin = auth()->check() && auth()->user()->is_admin;

        $media = $isAdmin
            ? $subspecies->media()->orderBy('moderation_status')->latest()->get()
            : $subspecies->approvedMedia()->latest()->get();

        $subspecies->load('species');

        return view('subspecies.show', compact('subspecies', 'media', 'isAdmin'));
    }

    public function storeMedia(Request $request, Subspecies $subspecies): RedirectResponse
    {
        $request->validate([
            'images'   => ['required', 'array', 'min:1'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
        ]);

        foreach ($request->file('images', []) as $file) {
            $path = $file->store('subspecies', 's3');
            $subspecies->media()->create([
                'url'               => Storage::disk('s3')->url($path),
                'user_id'           => auth()->id(),
                'moderation_status' => auth()->user()->is_admin ? 'approved' : 'pending',
            ]);
        }

        return back()->with('success', 'Photo(s) submitted for review. They will appear once approved.');
    }
}
