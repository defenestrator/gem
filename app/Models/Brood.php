<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brood extends Model
{
    use HasFactory;

    public function pairing()
    {
        return $this->belongsToMany(Pairing::class);
    }
}