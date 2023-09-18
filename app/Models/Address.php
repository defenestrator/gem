<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Dyrynda\Database\Support\GeneratesUuid;
use Dyrynda\Database\Casts\EfficientUuid;

/**
 * App\Models\Address
 *
 * @property int $id
 * @property mixed $uuid
 * @property string $addressable_type
 * @property int $addressable_id
 * @property string|null $nickname
 * @property string $country
 * @property string $street_address
 * @property string|null $unit_number
 * @property string|null $city
 * @property string|null $state
 * @property string|null $postal_code
 * @property float|null $latitude
 * @property float|null $longitude
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $addressable
 * @method static \Database\Factories\AddressFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Address newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Address newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Address query()
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereAddressableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereAddressableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereStreetAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereUnitNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereUuid($value)
 * @mixin \Eloquent
 */
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
