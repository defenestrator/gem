<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClassifiedRequest;
use App\Http\Requests\UpdateClassifiedRequest;
use App\Models\Classified;

class DashboardClassifiedController extends Controller
{
    public function index()
    {
        $classifieds = auth()->user()->classifieds()
            ->with('user', 'media')
            ->latest()
            ->paginate(12);

        return view('dashboard.classifieds.index', ['classifieds' => $classifieds]);
    }

    public function create()
    {
        $this->authorize('create', Classified::class);

        return view('dashboard.classifieds.create');
    }

    public function store(StoreClassifiedRequest $request)
    {
        $this->authorize('create', Classified::class);

        $classified = auth()->user()->classifieds()->create(
            $request->safe()->except(['images'])
        );

        foreach ($request->file('images', []) as $file) {
            $path = $file->store('images', 'public');
            $classified->media()->create([
                'url'     => url("storage/{$path}"),
                'user_id' => auth()->id(),
            ]);
        }

        return redirect()->route('dashboard.classifieds.show', $classified)
            ->with('success', 'Classified ad created successfully.');
    }

    public function show(Classified $classified)
    {
        $this->authorize('view', $classified);

        return view('dashboard.classifieds.show', [
            'classified' => $classified->load('user', 'media'),
        ]);
    }

    public function edit(Classified $classified)
    {
        $this->authorize('update', $classified);

        return view('dashboard.classifieds.edit', [
            'classified' => $classified->load('media'),
        ]);
    }

    public function update(UpdateClassifiedRequest $request, Classified $classified)
    {
        $this->authorize('update', $classified);

        $classified->update($request->safe()->except(['images']));

        foreach ($request->file('images', []) as $file) {
            $path = $file->store('images', 'public');
            $classified->media()->create([
                'url'     => url("storage/{$path}"),
                'user_id' => auth()->id(),
            ]);
        }

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
