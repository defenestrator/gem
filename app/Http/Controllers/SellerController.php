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

        $query = Seller::query()
            ->with('user')
            ->addSelect('sellers.*')
            ->when($search, fn ($q) => $q
                ->where('sellers.name', 'ilike', "%{$search}%")
                ->orWhere('sellers.description', 'ilike', "%{$search}%"))
            ->orderBy('sellers.name');

        if (config('features.classifieds')) {
            $query->selectSub(
                Classified::query()
                    ->selectRaw('count(*)')
                    ->whereColumn('classifieds.user_id', 'sellers.user_id')
                    ->where('classifieds.status', 'published'),
                'classifieds_count'
            );
        }

        $sellers = $query->paginate(18)->withQueryString();

        return view('sellers.index', compact('sellers', 'search'));
    }

    public function show(Seller $seller)
    {
        $seller->load('user');

        $classifieds = (config('features.classifieds') && $seller->user)
            ? $seller->user->classifieds()
                ->where('status', 'published')
                ->with('media')
                ->latest()
                ->paginate(12)
            : collect();

        return view('sellers.show', compact('seller', 'classifieds'));
    }
}
