<?php

namespace App\Http\Controllers;

use App\Enums\AnimalAvailability;
use App\Models\Animal;
use Illuminate\Http\Request;

class AnimalController extends Controller
{
    public function index(Request $request)
    {
        $sort         = $request->query('sort', 'recent');
        $search       = $request->query('search');
        $availability = $request->query('availability');

        $query = Animal::query()
            ->where('status', 'published')
            ->with('media', 'species');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('pet_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($availability && AnimalAvailability::tryFrom($availability)) {
            $query->where('availability', $availability);
        }

        match ($sort) {
            'name-asc'  => $query->orderBy('pet_name', 'asc'),
            'name-desc' => $query->orderBy('pet_name', 'desc'),
            'oldest'    => $query->oldest(),
            default     => $query->latest(),
        };

        return view('animals.index', [
            'animals'      => $query->paginate(24)->withQueryString(),
            'currentSort'  => $sort,
            'search'       => $search,
            'availability' => $availability,
            'availabilities' => AnimalAvailability::cases(),
        ]);
    }

    public function show(Animal $animal)
    {
        $this->authorize('view', $animal);

        return view('animals.show', ['animal' => $animal->load('user', 'media', 'species')]);
    }
}
