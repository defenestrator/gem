<?php
namespace App\Models\Traits;

use Illuminate\Support\Str;

trait hasMedia {

    public function media()
    {
        return $this->morphMany(Image::class, 'mediable');
    }
}