<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeedingRequest;
use App\Http\Requests\UpdateFeedingRequest;
use App\Models\Feeding;

class FeedingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFeedingRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Feeding $feeding)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Feeding $feeding)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFeedingRequest $request, Feeding $feeding)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Feeding $feeding)
    {
        //
    }
}
