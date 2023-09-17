<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Dyrynda\Database\Support\GeneratesUuid;
use Dyrynda\Database\Casts\EfficientUuid;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Http\UploadedFile;

class Media extends Model
{
    use HasFactory, GeneratesUuid;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    protected $fillable = [
        'url', 'license', 'copyright', 'author', 'title'
    ];

    public function mediable()
    {
        return $this->morphTo();
    }

    /**
     * Scope by Mediable Type
     *
     * $value can be a namespaced class string, or a POPO class
     *
     * @param Builder $query
     * @param string $value
     * @return Builder $query
     */
    public function scopeType(Builder $query, $value)
    {
        return $query->where('mediable_type', $value);
    }

    /**
     * Scope by Animal
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeAnimals($query)
    {
        return $this->ScopeByType($query, "App\Animal");
    }

    /**
     * Scope by Species
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeSpecies($query)
    {
        return $this->scopeByType($query, "App\Species");
    }

    /**
     * Upload Media
     *
     * @param  mixed $file
     *
     * @return string $newImage
     */
    protected function uploadImage(UploadedFile $file, $size = 2000)
    {
        $exif = exif_read_data($file, 0, true);

        $title = $file->originalName;

        $location = $this->yeetImageLocation($exif);

        $copyright = '';

        $license = '';

        $lat = $long = null;

        $newImage = $this->processImage($file, $size);

        if ($location != false && is_array($location)) {
            $lat = $location->latitude;
            $long = $location->longitude;
        }


        $this->forceFill([
            $this->url => $newImage,
            $this->exif => $exif,
            $this->latitude => $lat,
            $this->longitude => $long,
            $this->title => $title,
            $this->license => $license,
            $this->copyright => $copyright
        ])->save();

        return $newImage;
    }

    public function gps2Num($coordPart)
    {
        $parts = explode('/', $coordPart);

        if(count($parts) <= 0)
            return 0;
        if(count($parts) == 1)
            return $parts[0];

        return floatval($parts[0]) / floatval($parts[1]);
    }

    /**
     * processMedia
     *
     * @param  mixed $media
     * @param  mixed $size
     *
     * @return array $filePath
     */
    protected function processMedia($media, $size)
    {
        $options = [
            'visibility'    =>  'public',
            'Cache-Control' =>  'max-age=315400000',
            'Expires'       =>  now()->addRealDecade()->format('D, d M Y H:i:s T')
        ];

        $i = Image::make($media)
            ->resize($size, $size, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode('webp')
            ->stream();

        $hash = Str::uuid();
        $fileName = $hash . '.webp';
        if (config('app.env') == 'production') {
            Storage::disk('s3')->getDriver()->put('/images/'. $fileName, $i->__toString(), $options);
            $filePath = config('filesystems.disks.s3.url' . '/images/', 'https://gemx.sfo3.cdn.digitaloceanspaces.com/images/') . $fileName;
        } else {
            Storage::disk('local')->put('/images/'. $fileName, $i->__toString());
            $filePath = Storage::disk(env('ASSET_URL', 'local'))->url('images/'. $fileName);
        }

        return $filePath;
    }

    /**
     * yeetImageLocation
     * Returns an array of latitude and longitude 
     * from the Media file by looking for various possible data
     * @param $exif
     * @return multitype:array|boolean
     */
    protected function yeetImageLocation($exif = '')
    {
        if (isset($exif['GPS'])) {
            $GPSLatitudeRef = [$exif['GPS']['GPSLatitudeRef']];
            $GPSLatitude    = [$exif['GPS']['GPSLatitude']];
            $GPSLongitudeRef= [$exif['GPS']['GPSLongitudeRef']];
            $GPSLongitude   = [$exif['GPS']['GPSLongitude']];

            $lat_degrees = count($GPSLatitude) > 0 ? $this->gps2Num($GPSLatitude[0]) : 0;
            $lat_minutes = count($GPSLatitude) > 1 ? $this->gps2Num($GPSLatitude[1]) : 0;
            $lat_seconds = count($GPSLatitude) > 2 ? $this->gps2Num($GPSLatitude[2]) : 0;

            $lon_degrees = count($GPSLongitude) > 0 ? $this->gps2Num($GPSLongitude[0]) : 0;
            $lon_minutes = count($GPSLongitude) > 1 ? $this->gps2Num($GPSLongitude[1]) : 0;
            $lon_seconds = count($GPSLongitude) > 2 ? $this->gps2Num($GPSLongitude[2]) : 0;

            $lat_direction = ($GPSLatitudeRef == 'W' or $GPSLatitudeRef == 'S') ? -1 : 1;
            $lon_direction = ($GPSLongitudeRef == 'W' or $GPSLongitudeRef == 'S') ? -1 : 1;

            $latitude = $lat_direction * ($lat_degrees + ($lat_minutes / 60) + ($lat_seconds / (60*60)));
            $longitude = $lon_direction * ($lon_degrees + ($lon_minutes / 60) + ($lon_seconds / (60*60)));

            return ['latitude'=>$latitude, 'longitude'=>$longitude];
        } else {
            return false;
        }
    }

}
