<?php
namespace App\Models\Traits;

use Illuminate\Support\Str;
use App\Models\Media;

trait HasMedia {

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function featuredMedia()
    {
        return $this->morphOne(Media::class, 'mediable')->where('is_featured', true);
    }
}