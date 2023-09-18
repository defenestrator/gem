<?php
namespace App\Traits;

use Illuminate\Support\Str;

trait Sluggable {

    public static function createSlug($text)
    {
        return Str::slug($text);
    }

    public static function getTitleFromSlug($slug)
    {
        return ucwords(preg_replace('~-~', ' ', $slug));
    }
}
