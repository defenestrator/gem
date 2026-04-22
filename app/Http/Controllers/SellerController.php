<?php

namespace App\Http\Controllers;

use App\Models\Classified;
use App\Models\Seller;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $sellers = Seller::query()
            ->with('user')
            ->selectSub(
                Classified::query()
                    ->selectRaw('count(*)')
                    ->whereColumn('classifieds.user_id', 'sellers.user_id')
                    ->where('classifieds.status', 'published'),
                'classifieds_count'
            )
            ->addSelect('sellers.*')
            ->when($search, fn ($q) => $q
                ->where('sellers.name', 'ilike', "%{$search}%")
                ->orWhere('sellers.description', 'ilike', "%{$search}%"))
            ->orderBy('sellers.name')
            ->paginate(18)
            ->withQueryString();

        return view('sellers.index', compact('sellers', 'search'));
    }

    public function show(Seller $seller)
    {
        $seller->load('user');

        $classifieds = $seller->user
            ? $seller->user->classifieds()
                ->where('status', 'published')
                ->with('media')
                ->latest()
                ->paginate(12)
            : collect();

        return view('sellers.show', compact('seller', 'classifieds'));
    }
}
