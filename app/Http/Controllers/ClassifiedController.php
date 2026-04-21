<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClassifiedRequest;
use App\Http\Requests\UpdateClassifiedRequest;
use App\Models\Animal;
use App\Models\Classified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class ClassifiedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $currentRoute = Route::currentRouteName();
        
        // Dashboard view - authenticated user's classifieds
        if (str_starts_with($currentRoute, 'dashboard.')) {
            $classifieds = auth()->user()->classifieds()
                ->with('animal', 'user')
                ->latest()
                ->paginate(12);
            
            return view('classifieds.index', [
                'classifieds' => $classifieds,
                'isDashboard' => true,
            ]);
        }
        
        // Public view - published classifieds with filters
        $sort = $request->query('sort', 'recent');
        $categoryFilter = $request->query('category', null);
        $minPrice = $request->query('min_price', null);
        $maxPrice = $request->query('max_price', null);
        $search = $request->query('search', null);

        $query = Classified::where('status', 'published')
            ->with('animal', 'user');

        if ($categoryFilter) {
            $query->whereHas('animal', function ($q) use ($categoryFilter) {
                $q->where('id', $categoryFilter);
            });
        }

        if ($minPrice !== null) {
            $query->where('price', '>=', $minPrice);
        }

        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }

        if ($search) {
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        // Apply sorting
        match ($sort) {
            'price-low' => $query->orderBy('price', 'asc'),
            'price-high' => $query->orderBy('price', 'desc'),
            'oldest' => $query->oldest(),
            default => $query->latest(), // 'recent'
        };

        $classifieds = $query->paginate(12);
        $animals = Animal::all();

        return view('classifieds.index', [
            'classifieds' => $classifieds,
            'isDashboard' => false,
            'currentSort' => $sort,
            'animals' => $animals,
            'categoryFilter' => $categoryFilter,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Classified::class);
        $animals = Animal::all();

        return view('classifieds.create', ['animals' => $animals]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClassifiedRequest $request)
    {
        $this->authorize('create', Classified::class);

        $classified = auth()->user()->classifieds()->create(
            $request->validated()
        );

        return redirect()->route('dashboard.classifieds.show', $classified)
            ->with('success', 'Classified ad created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Classified $classified)
    {
        $this->authorize('view', $classified);

        return view('classifieds.show', ['classified' => $classified->load('animal', 'user')]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Classified $classified)
    {
        $this->authorize('update', $classified);
        $animals = Animal::all();

        return view('classifieds.edit', [
            'classified' => $classified,
            'animals' => $animals,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClassifiedRequest $request, Classified $classified)
    {
        $this->authorize('update', $classified);

        $classified->update($request->validated());

        return redirect()->route('dashboard.classifieds.show', $classified)
            ->with('success', 'Classified ad updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Classified $classified)
    {
        $this->authorize('delete', $classified);

        $classified->delete();

        return redirect()->route('dashboard.classifieds.index')
            ->with('success', 'Classified ad deleted successfully.');
    }
}
