<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Socialite\Two\User as SocialiteUser;

class SocialAccount extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'name',
        'email',
        'nickname',
        'avatar',
        'token',
        'refresh_token',
        'token_expires_at',
        'provider_data',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'provider_data'    => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function fromSocialite(string $provider, SocialiteUser $social): array
    {
        $raw = $social->getRaw();

        return [
            'provider'         => $provider,
            'provider_id'      => $social->getId(),
            'name'             => $social->getName(),
            'email'            => $social->getEmail(),
            'nickname'         => $social->getNickname(),
            'avatar'           => $social->getAvatar(),
            'token'            => $social->token,
            'refresh_token'    => $social->refreshToken ?? null,
            'token_expires_at' => isset($social->expiresIn)
                ? now()->addSeconds($social->expiresIn)
                : null,
            'provider_data'    => $raw,
        ];
    }
}
