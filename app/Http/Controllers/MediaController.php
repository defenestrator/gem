<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMediaRequest;
use App\Http\Requests\UpdateMediaRequest;
use App\Models\Media;

class MediaController extends Controller
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
    public function store(StoreMediaRequest $request, Media $media)
    {
        if ($request->hasFile('media') && $request->file('media')->isValid() ) {

            $model = [
                'mediable_type' => $request->imageable_type,
                'mediable_id' => $request->imageable_id
            ];

            $file = $media->uploadImage($request->file('media'), $model);

            return $media->create($file);
        }

        return response('Error', 422, [
            'error' => 'The file you uploaded is an unsupported file type or corrupted.'
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show(Media $media)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Media $media)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMediaRequest $request, Media $media)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Media $media)
    {
        //
    }
}
