<?php

namespace App\Enums;

enum AnimalAvailability: string
{
    case ForSale    = 'for_sale';
    case OnHold     = 'on_hold';
    case Breeder    = 'breeder';
    case Sold       = 'sold';
    case NotForSale = 'not_for_sale';

    public function label(): string
    {
        return match ($this) {
            self::ForSale    => 'For Sale',
            self::OnHold     => 'On Hold',
            self::Breeder    => 'Breeder',
            self::Sold       => 'Sold',
            self::NotForSale => 'Not For Sale',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::ForSale    => 'bg-green-600 text-white dark:bg-green-700',
            self::OnHold     => 'bg-yellow-500 text-white dark:bg-yellow-600',
            self::Breeder    => 'bg-black text-white',
            self::Sold       => 'bg-red-600 text-white dark:bg-red-700',
            self::NotForSale => 'bg-gray-500 text-white dark:bg-gray-600',
        };
    }

    public static function fromJsonState(string $state): ?self
    {
        return match ($state) {
            'For Sale'     => self::ForSale,
            'On Hold'      => self::OnHold,
            'Breeder'      => self::Breeder,
            'Sold'         => self::Sold,
            'Not For Sale' => self::NotForSale,
            default        => null,
        };
    }
}
