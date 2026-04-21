<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Classified;
use Illuminate\Http\Request;

class ClassifiedController extends Controller
{
    public function index(Request $request)
    {
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

        match ($sort) {
            'price-low' => $query->orderBy('price', 'asc'),
            'price-high' => $query->orderBy('price', 'desc'),
            'oldest' => $query->oldest(),
            default => $query->latest(),
        };

        $classifieds = $query->paginate(12);
        $animals = Animal::all();

        return view('classifieds.index', [
            'classifieds' => $classifieds,
            'currentSort' => $sort,
            'animals' => $animals,
            'categoryFilter' => $categoryFilter,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'search' => $search,
        ]);
    }

    public function show(Classified $classified)
    {
        $this->authorize('view', $classified);

        return view('classifieds.show', ['classified' => $classified->load('animal', 'user')]);
    }
}
