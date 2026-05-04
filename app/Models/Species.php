<?php

namespace App\Models;

use App\Casts\SpeciesTypeCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasMedia;

class Species extends Model
{
    use HasFactory, HasMedia;

    protected $fillable = [
        'type_species',
        'species',
        'author',
        'subspecies',
        'common_name',
        'higher_taxa',
        'species_number',
        'changes',
        'description',
    ];

    protected $casts = [
        'type_species' => SpeciesTypeCast::class,
    ];
}
