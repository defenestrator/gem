<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function destroy(Media $media)
    {
        $mediable = $media->mediable;

        if (!$mediable || $mediable->user_id !== auth()->id()) {
            abort(403);
        }

        // Derive storage-relative path from the stored public URL
        $relativePath = ltrim(
            str_replace(Storage::disk('public')->url(''), '', $media->url),
            '/'
        );

        if ($relativePath && Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }

        $media->delete();

        return back()->with('success', 'Image deleted.');
    }
}
