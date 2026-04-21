<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnimalRequest;
use App\Http\Requests\UpdateAnimalRequest;
use App\Models\Animal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class AnimalController extends Controller
{
    public function index(Request $request)
    {
        $currentRoute = Route::currentRouteName();

        if (str_starts_with($currentRoute, 'dashboard.')) {
            $animals = auth()->user()->animals()
                ->with('media')
                ->latest()
                ->paginate(12);

            return view('animals.index', [
                'animals'     => $animals,
                'isDashboard' => true,
            ]);
        }

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
            'isDashboard' => false,
            'currentSort' => $sort,
            'search'      => $search,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Animal::class);

        return view('animals.create');
    }

    public function store(StoreAnimalRequest $request)
    {
        $this->authorize('create', Animal::class);

        $animal = auth()->user()->animals()->create($request->validated());

        return redirect()->route('dashboard.animals.show', $animal)
            ->with('success', 'Animal created successfully.');
    }

    public function show(Animal $animal)
    {
        $this->authorize('view', $animal);

        return view('animals.show', ['animal' => $animal->load('user', 'media')]);
    }

    public function edit(Animal $animal)
    {
        $this->authorize('update', $animal);

        return view('animals.edit', ['animal' => $animal]);
    }

    public function update(UpdateAnimalRequest $request, Animal $animal)
    {
        $this->authorize('update', $animal);

        $animal->update($request->validated());

        return redirect()->route('dashboard.animals.show', $animal)
            ->with('success', 'Animal updated successfully.');
    }

    public function destroy(Animal $animal)
    {
        $this->authorize('delete', $animal);

        $animal->delete();

        return redirect()->route('dashboard.animals.index')
            ->with('success', 'Animal deleted successfully.');
    }
}
