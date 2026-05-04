<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Http\UploadedFile;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'url', 'user_id', 'license', 'license_url', 'source_url', 'copyright', 'author', 'title', 'moderation_status',
    ];

    public function mediable()
    {
        return $this->morphTo();
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('moderation_status', 'approved');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('moderation_status', 'pending');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('moderation_status', 'rejected');
    }

    public function scopeType(Builder $query, $value): Builder
    {
        return $query->where('mediable_type', $value);
    }

    public function scopeAnimals(Builder $query): Builder
    {
        return $this->scopeType($query, "App\Animal");
    }

    public function scopeSpecies(Builder $query): Builder
    {
        return $this->scopeType($query, "App\Species");
    }

    public function scopeFieldObservations(Builder $query): Builder
    {
        return $this->scopeType($query, "App\FieldObservation");
    }

    protected function uploadImage(UploadedFile $file, $size = 2000)
    {
        $exif = exif_read_data($file, 0, true);

        $title = $file->getClientOriginalName();

        $location = $this->yeetImageLocation($exif);

        $lat = $long = null;

        $newImage = $this->processMedia($file, $size);

        if ($location != false && is_array($location)) {
            $lat = $location['latitude'];
            $long = $location['longitude'];
        }

        $this->forceFill([
            'url'       => $newImage,
            'title'     => $title,
            'license'   => '',
            'copyright' => '',
        ])->save();

        return $newImage;
    }

    public function gps2Num($coordPart)
    {
        $parts = explode('/', $coordPart);

        if (count($parts) <= 0) return 0;
        if (count($parts) == 1) return $parts[0];

        return floatval($parts[0]) / floatval($parts[1]);
    }

    protected function processMedia($media, $size)
    {
        $options = [
            'visibility'    => 'public',
            'Cache-Control' => 'max-age=315400000',
            'Expires'       => now()->addRealDecade()->format('D, d M Y H:i:s T'),
        ];

        $i = Image::make($media)
            ->resize($size, $size, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode('webp')
            ->stream();

        $hash = Str::random();
        $fileName = $hash . '.webp';

        if (config('app.env') == 'production') {
            Storage::disk('s3')->getDriver()->put('/images/' . $fileName, $i->__toString(), $options);
            $filePath = config('filesystems.disks.s3.url', 'https://gemx.sfo3.cdn.digitaloceanspaces.com') . '/images/' . $fileName;
        } else {
            Storage::disk('local')->put('/images/' . $fileName, $i->__toString());
            $filePath = Storage::disk('public')->url('/images/' . $fileName);
        }

        return $filePath;
    }

    protected function yeetImageLocation($exif = '')
    {
        if (isset($exif['GPS'])) {
            $GPSLatitudeRef  = $exif['GPS']['GPSLatitudeRef'];
            $GPSLatitude     = $exif['GPS']['GPSLatitude'];
            $GPSLongitudeRef = $exif['GPS']['GPSLongitudeRef'];
            $GPSLongitude    = $exif['GPS']['GPSLongitude'];

            $lat_degrees = count($GPSLatitude) > 0 ? $this->gps2Num($GPSLatitude[0]) : 0;
            $lat_minutes = count($GPSLatitude) > 1 ? $this->gps2Num($GPSLatitude[1]) : 0;
            $lat_seconds = count($GPSLatitude) > 2 ? $this->gps2Num($GPSLatitude[2]) : 0;

            $lon_degrees = count($GPSLongitude) > 0 ? $this->gps2Num($GPSLongitude[0]) : 0;
            $lon_minutes = count($GPSLongitude) > 1 ? $this->gps2Num($GPSLongitude[1]) : 0;
            $lon_seconds = count($GPSLongitude) > 2 ? $this->gps2Num($GPSLongitude[2]) : 0;

            $lat_direction = ($GPSLatitudeRef == 'W' or $GPSLatitudeRef == 'S') ? -1 : 1;
            $lon_direction = ($GPSLongitudeRef == 'W' or $GPSLongitudeRef == 'S') ? -1 : 1;

            $latitude  = $lat_direction * ($lat_degrees + ($lat_minutes / 60) + ($lat_seconds / (60 * 60)));
            $longitude = $lon_direction * ($lon_degrees + ($lon_minutes / 60) + ($lon_seconds / (60 * 60)));

            return ['latitude' => $latitude, 'longitude' => $longitude];
        }

        return false;
    }
}
