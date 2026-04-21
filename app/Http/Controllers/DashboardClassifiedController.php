<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClassifiedRequest;
use App\Http\Requests\UpdateClassifiedRequest;
use App\Models\Animal;
use App\Models\Classified;

class DashboardClassifiedController extends Controller
{
    public function index()
    {
        $classifieds = auth()->user()->classifieds()
            ->with('animal', 'user')
            ->latest()
            ->paginate(12);

        return view('dashboard.classifieds.index', ['classifieds' => $classifieds]);
    }

    public function create()
    {
        $this->authorize('create', Classified::class);
        $animals = Animal::all();

        return view('dashboard.classifieds.create', ['animals' => $animals]);
    }

    public function store(StoreClassifiedRequest $request)
    {
        $this->authorize('create', Classified::class);

        $classified = auth()->user()->classifieds()->create($request->validated());

        return redirect()->route('dashboard.classifieds.show', $classified)
            ->with('success', 'Classified ad created successfully.');
    }

    public function show(Classified $classified)
    {
        $this->authorize('view', $classified);

        return view('dashboard.classifieds.show', ['classified' => $classified->load('animal', 'user')]);
    }

    public function edit(Classified $classified)
    {
        $this->authorize('update', $classified);
        $animals = Animal::all();

        return view('dashboard.classifieds.edit', [
            'classified' => $classified,
            'animals' => $animals,
        ]);
    }

    public function update(UpdateClassifiedRequest $request, Classified $classified)
    {
        $this->authorize('update', $classified);

        $classified->update($request->validated());

        return redirect()->route('dashboard.classifieds.show', $classified)
            ->with('success', 'Classified ad updated successfully.');
    }

    public function destroy(Classified $classified)
    {
        $this->authorize('delete', $classified);

        $classified->delete();

        return redirect()->route('dashboard.classifieds.index')
            ->with('success', 'Classified ad deleted successfully.');
    }
}
