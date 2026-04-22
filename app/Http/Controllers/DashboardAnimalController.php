<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnimalRequest;
use App\Http\Requests\UpdateAnimalRequest;
use App\Models\Animal;
use Illuminate\Support\Facades\Storage;

class DashboardAnimalController extends Controller
{
    public function index()
    {
        $animals = auth()->user()->animals()
            ->with('media')
            ->latest()
            ->paginate(12);

        return view('dashboard.animals.index', ['animals' => $animals]);
    }

    public function create()
    {
        $this->authorize('create', Animal::class);

        return view('dashboard.animals.create');
    }

    public function store(StoreAnimalRequest $request)
    {
        $this->authorize('create', Animal::class);

        $animal = auth()->user()->animals()->create(
            $request->safe()->except(['images'])
        );

        foreach ($request->file('images', []) as $file) {
            $path = $file->store('images', 'public');
            $animal->media()->create(['url' => Storage::disk('public')->url($path)]);
        }

        return redirect()->route('dashboard.animals.show', $animal)
            ->with('success', 'Animal created successfully.');
    }

    public function show(Animal $animal)
    {
        $this->authorize('view', $animal);

        return view('dashboard.animals.show', ['animal' => $animal->load('user', 'media')]);
    }

    public function edit(Animal $animal)
    {
        $this->authorize('update', $animal);

        return view('dashboard.animals.edit', ['animal' => $animal->load('media')]);
    }

    public function update(UpdateAnimalRequest $request, Animal $animal)
    {
        $this->authorize('update', $animal);

        $animal->update($request->safe()->except(['images']));

        foreach ($request->file('images', []) as $file) {
            $path = $file->store('images', 'public');
            $animal->media()->create(['url' => Storage::disk('public')->url($path)]);
        }

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
