<?php
namespace App\Models\Traits;

use Illuminate\Support\Str;

trait HasMedia {

    public function media()
    {
        return $this->morphMany(Image::class, 'mediable');
    }
}