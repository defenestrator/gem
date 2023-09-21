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
    public function store(StoreMediaRequest $request)
    {
        /** 
         * Media Processing Service Design Notes
         * 
         * Validate the input file, obviously, try to determine the upload veracity
         * then route through the correct processing pipeline for the mimetype
         * accept images, videos, .pdf, and as many file types that can be 
         * sanitized and stored without getting the servers hacked
         * generate base64 url-encoded preview thumbnail
         * route to queue to process to safe, sanitized
         * resized, compressed, tweezed and otherwise 
         * desirable final file format 
         * format upload on spaces
         * this feels like 
        */ 
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
