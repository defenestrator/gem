<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Species;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SpeciesController extends Controller
{
    public function index(): View
    {
        return view('species.index');
    }

    public function show(Species $species): View
    {
        $isAdmin = auth()->check() && auth()->user()->is_admin;

        $media = $isAdmin
            ? $species->media()->orderBy('moderation_status')->latest()->get()
            : $species->approvedMedia()->latest()->get();

        $subspecies = $species->subspecies()->orderBy('subspecies')->get();

        return view('species.show', compact('species', 'media', 'isAdmin', 'subspecies'));
    }

    public function storeMedia(Request $request, Species $species): RedirectResponse
    {
        $request->validate([
            'images'   => ['required', 'array', 'min:1'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
        ]);

        foreach ($request->file('images', []) as $file) {
            $path = $file->store('species', 's3');
            $species->media()->create([
                'url'               => Storage::disk('s3')->url($path),
                'user_id'           => auth()->id(),
                'moderation_status' => auth()->user()->is_admin ? 'approved' : 'pending',
            ]);
        }

        return back()->with('success', 'Photo(s) submitted for review. They will appear once approved.');
    }

    public function search(Request $request): JsonResponse
    {
        $query = trim($request->string('q'));

        if ($query === '') {
            return response()->json(['results' => [], 'query' => '']);
        }

        $cacheKey = 'species.search.' . md5(mb_strtolower($query));

        $results = Cache::remember($cacheKey, 300, function () use ($query) {
            try {
                return Species::search($query)
                    ->take(60)
                    ->get()
                    ->map(fn (Species $s) => $this->format($s))
                    ->values()
                    ->all();
            } catch (\Throwable $e) {
                Log::warning('Species MeiliSearch unavailable, falling back to DB search.', [
                    'error' => $e->getMessage(),
                ]);

                return Species::query()
                    ->where('species', 'like', "%{$query}%")
                    ->orWhere('common_name', 'like', "%{$query}%")
                    ->orWhere('higher_taxa', 'like', "%{$query}%")
                    ->limit(60)
                    ->get()
                    ->map(fn (Species $s) => $this->format($s))
                    ->values()
                    ->all();
            }
        });

        return response()->json(['results' => $results, 'query' => $query]);
    }

    private function format(Species $s): array
    {
        return [
            'id'             => $s->id,
            'species'        => $s->species,
            'common_name'    => $s->common_name,
            'higher_taxa'    => $s->higher_taxa,
            'author'         => $s->author,
            'species_number' => $s->species_number,
            'type_species'   => $s->getRawOriginal('type_species'),
        ];
    }
}
