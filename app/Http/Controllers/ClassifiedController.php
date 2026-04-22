<?php

namespace App\Http\Controllers;

use App\Models\Classified;
use Illuminate\Http\Request;

class ClassifiedController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->query('sort', 'recent');
        $minPrice = $request->query('min_price', null);
        $maxPrice = $request->query('max_price', null);
        $search = $request->query('search', null);

        $query = Classified::where('status', 'published')
            ->with('user', 'media');

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

        return view('classifieds.index', [
            'classifieds' => $classifieds,
            'currentSort' => $sort,
            'minPrice'    => $minPrice,
            'maxPrice'    => $maxPrice,
            'search'      => $search,
        ]);
    }

    public function show(Classified $classified)
    {
        $this->authorize('view', $classified);

        return view('classifieds.show', ['classified' => $classified->load('user', 'media')]);
    }
}
