<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Dyrynda\Database\Support\GeneratesUuid;
use Dyrynda\Database\Casts\EfficientUuid;

class Address extends Model
{
    use HasFactory, GeneratesUuid;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    protected $fillable = [
        'nickname',
        'country',
        'street_address',
        'unit_number',
        'city',
        'state',
        'postal_code',
        'latitude',
        'longitude'
    ];

    public function addressable()
    {
        return $this->morphTo();
    }
}
