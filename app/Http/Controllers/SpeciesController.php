<?php

namespace App\Http\Controllers;

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
                $rows = Species::search($query)->take(60)->get();
                $rows->loadMissing('latestApprovedMedia');

                return $rows->map(fn (Species $s) => $this->format($s))->values()->all();
            } catch (\Throwable $e) {
                Log::warning('Species MeiliSearch unavailable, falling back to DB search.', [
                    'error' => $e->getMessage(),
                ]);

                $term  = strtolower($query);
                $exact = $term;
                $start = $term . '%';
                $any   = '%' . $term . '%';

                $rows = Species::query()
                    ->where(fn ($q) => $q
                        ->where('common_name', 'like', $any)
                        ->orWhere('species', 'like', $any)
                        ->orWhere('higher_taxa', 'like', $any)
                    )
                    ->orderByRaw("
                        CASE
                            WHEN LOWER(common_name) = ?        THEN 1
                            WHEN LOWER(species)     = ?        THEN 2
                            WHEN LOWER(common_name) LIKE ?     THEN 3
                            WHEN LOWER(species)     LIKE ?     THEN 4
                            WHEN LOWER(common_name) LIKE ?     THEN 5
                            WHEN LOWER(species)     LIKE ?     THEN 6
                            ELSE 7
                        END
                    ", [$exact, $exact, $start, $start, $any, $any])
                    ->limit(60)
                    ->get();

                $rows->loadMissing('latestApprovedMedia');

                return $rows->map(fn (Species $s) => $this->format($s))->values()->all();
            }
        });

        return response()->json(['results' => $results, 'query' => $query]);
    }

    private function format(Species $s): array
    {
        return [
            'id'          => $s->id,
            'species'     => $s->species,
            'common_name' => $s->common_name,
            'higher_taxa' => $s->higher_taxa,
            'author'      => $s->author,
            'thumbnail'   => $s->latestApprovedMedia?->url,
        ];
    }
}
