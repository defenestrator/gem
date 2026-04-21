<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use Illuminate\Http\Request;

class AnimalController extends Controller
{
    public function index(Request $request)
    {
        $sort   = $request->query('sort', 'recent');
        $search = $request->query('search');

        $query = Animal::where('status', 'published')->with('media');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('pet_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        match ($sort) {
            'name-asc'  => $query->orderBy('pet_name', 'asc'),
            'name-desc' => $query->orderBy('pet_name', 'desc'),
            'oldest'    => $query->oldest(),
            default     => $query->latest(),
        };

        return view('animals.index', [
            'animals'     => $query->paginate(12),
            'currentSort' => $sort,
            'search'      => $search,
        ]);
    }

    public function show(Animal $animal)
    {
        $this->authorize('view', $animal);

        return view('animals.show', ['animal' => $animal->load('user', 'media')]);
    }
}
