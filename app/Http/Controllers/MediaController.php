<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function attribution(Media $media): View
    {
        $mediable = $media->mediable;

        // Only show attribution for approved species/subspecies images that have source data
        if (! $mediable || $media->moderation_status !== 'approved') {
            abort(404);
        }

        return view('media.attribution', compact('media', 'mediable'));
    }

    public function destroy(Media $media)
    {
        $isAdmin  = auth()->user()->is_admin;
        $mediable = $media->mediable;

        if (! $isAdmin && (! $mediable || $mediable->user_id !== auth()->id())) {
            abort(403);
        }

        $this->deleteFile($media->url);

        $media->delete();

        return back()->with('success', 'Image deleted.');
    }

    private function deleteFile(string $url): void
    {
        // Try S3 first (species/subspecies uploads)
        $s3Base = Storage::disk('s3')->url('');
        if ($s3Base && str_starts_with($url, $s3Base)) {
            $path = ltrim(str_replace($s3Base, '', $url), '/');
            if ($path) {
                Storage::disk('s3')->delete($path);
            }
            return;
        }

        // Fall back to public disk
        $publicBase = Storage::disk('public')->url('');
        $path       = ltrim(str_replace($publicBase, '', $url), '/');
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
