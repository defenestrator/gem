<?php
namespace App\Models\Traits;

use Illuminate\Support\Str;
use App\Models\Traits\HasMedia;

trait HasMedia {

    public function media()
    {
        return $this->morphMany(Image::class, 'mediable');
    }
}