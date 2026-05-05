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

    private const TAXON_PATTERNS = [
        'lizards'      => '%Sauria%',
        'snakes'       => '%Serpentes%',
        'geckos'       => '%(lizards: geckos)%',
        'turtles'      => '%Testudines%',
        'amphisbaenia' => '%Amphisbaenia%',
        'crocodilians' => '%Crocody%',
        'tuatara'      => '%Rhyncho%',
    ];

    public function search(Request $request): JsonResponse
    {
        $query    = trim($request->string('q'));
        $hasMedia = $request->boolean('has_media');
        $taxonKey = $request->input('taxon', '');
        $taxonKey = array_key_exists($taxonKey, self::TAXON_PATTERNS) ? $taxonKey : '';

        $page    = max(1, (int) $request->input('page', 1));
        $perPage = 100;

        $cacheKey = 'species.search.' . md5(
            mb_strtolower($query)
            . ($hasMedia ? ':media' : '')
            . ($taxonKey ? ':' . $taxonKey : '')
            . ':p' . $page
        );

        $payload = Cache::remember($cacheKey, 300, function () use ($query, $hasMedia, $taxonKey, $page, $perPage) {
            $taxonConstraint = function ($q) use ($taxonKey) {
                if ($taxonKey === '') {
                    return;
                }
                $q->where('higher_taxa', 'like', self::TAXON_PATTERNS[$taxonKey]);
            };

            // Taxa-only browse (no text query) — skip Scout, go straight to DB
            if ($query === '') {
                $paginator = Species::query()
                    ->tap($taxonConstraint)
                    ->when($hasMedia, fn ($q) => $q->whereHas('media', fn ($m) => $m->where('moderation_status', 'approved')))
                    ->orderBy('species')
                    ->paginate($perPage, ['*'], 'page', $page);

                $paginator->getCollection()->loadMissing('latestApprovedMedia');

                return $this->paginatedPayload($paginator);
            }

            try {
                $paginator = Species::search($query)
                    ->query(function ($q) use ($hasMedia, $taxonConstraint) {
                        if ($hasMedia) {
                            $q->whereHas('media', fn ($m) => $m->where('moderation_status', 'approved'));
                        }
                        $taxonConstraint($q);
                    })
                    ->paginate($perPage, 'page', $page);

                $paginator->getCollection()->loadMissing('latestApprovedMedia');

                return $this->paginatedPayload($paginator);
            } catch (\Throwable $e) {
                Log::warning('Species MeiliSearch unavailable, falling back to DB search.', [
                    'error' => $e->getMessage(),
                ]);

                $term  = strtolower($query);
                $exact = $term;
                $start = $term . '%';
                $any   = '%' . $term . '%';

                $paginator = Species::query()
                    ->where(fn ($q) => $q
                        ->where('common_name', 'like', $any)
                        ->orWhere('species', 'like', $any)
                        ->orWhere('higher_taxa', 'like', $any)
                    )
                    ->when($hasMedia, fn ($q) => $q->whereHas('media', fn ($m) => $m->where('moderation_status', 'approved')))
                    ->tap($taxonConstraint)
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
                    ->paginate($perPage, ['*'], 'page', $page);

                $paginator->getCollection()->loadMissing('latestApprovedMedia');

                return $this->paginatedPayload($paginator);
            }
        });

        return response()->json(array_merge($payload, ['query' => $query]));
    }

    private function paginatedPayload(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): array
    {
        return [
            'results' => $paginator->getCollection()
                ->map(fn (Species $s) => $this->format($s))
                ->values()
                ->all(),
            'meta' => [
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
            ],
        ];
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
