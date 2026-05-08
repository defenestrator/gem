<?php

namespace App\Http\Controllers;

use App\Enums\AnimalAvailability;
use App\Models\Animal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnimalController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        return view('animals.index', [
            'availabilities' => AnimalAvailability::cases(),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $query    = trim($request->string('q'));
        $sort     = $request->input('sort', 'recent');
        $avail    = $request->input('availability');
        $page     = max(1, (int) $request->input('page', 1));
        $perPage  = 24;

        $availEnum = $avail ? AnimalAvailability::tryFrom($avail) : null;

        $applyFilters = function ($q) use ($availEnum) {
            $q->where('status', 'published');
            if ($availEnum) {
                $q->where('availability', $availEnum->value);
            }
        };

        $applySort = function ($q) use ($sort) {
            match ($sort) {
                'name-asc'  => $q->orderBy('pet_name', 'asc'),
                'name-desc' => $q->orderBy('pet_name', 'desc'),
                'oldest'    => $q->oldest(),
                default     => $q->latest(),
            };
        };

        if ($query !== '') {
            try {
                $rows = Animal::search($query)
                    ->query(function ($q) use ($applyFilters) {
                        $applyFilters($q);
                        $q->with('media', 'species');
                    })
                    ->get();

                return response()->json([
                    'results' => $rows->map(fn ($a) => $this->format($a))->values(),
                    'meta'    => ['total' => $rows->count(), 'per_page' => $perPage, 'current_page' => 1, 'last_page' => 1],
                    'query'   => $query,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Animal MeiliSearch unavailable, falling back to DB.', ['error' => $e->getMessage()]);
            }
        }

        $q = Animal::query()->with('media', 'species');
        $applyFilters($q);

        if ($query !== '') {
            $q->where(function ($sub) use ($query) {
                $sub->where('pet_name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('category', 'like', "%{$query}%");
            });
        }

        $applySort($q);

        $paginator = $q->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'results' => $paginator->getCollection()->map(fn ($a) => $this->format($a))->values(),
            'meta'    => [
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
            ],
            'query' => $query,
        ]);
    }

    private function format(Animal $a): array
    {
        $thumb = $a->media->first();
        return [
            'id'           => $a->id,
            'slug'         => $a->slug,
            'pet_name'     => $a->pet_name,
            'category'     => $a->category,
            'description'  => $a->description ? \Illuminate\Support\Str::limit($a->description, 80) : null,
            'availability' => $a->availability?->value,
            'availability_label' => $a->availability?->label(),
            'availability_badge' => $a->availability?->badgeClasses(),
            'price'        => $a->price ? number_format((float) $a->price, 2) : null,
            'female'       => $a->female,
            'date_of_birth' => $a->date_of_birth?->format('M d, Y'),
            'species_name' => $a->species?->species,
            'species_slug' => $a->species?->slug,
            'thumbnail'    => $thumb?->thumbnail_url ?? $thumb?->url,
            'show_url'     => route('animals.show', $a->slug),
            'inquire_url'  => route('animals.inquiries.create', $a->slug),
            'species_url'  => ($a->species?->slug) ? route('species.show', $a->species->slug) : null,
        ];
    }

    public function show(Animal $animal)
    {
        $this->authorize('view', $animal);

        return view('animals.show', ['animal' => $animal->load('user', 'media', 'species')]);
    }
}
