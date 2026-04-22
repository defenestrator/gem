<?php

namespace App\Enums;

enum AnimalAvailability: string
{
    case ForSale    = 'for_sale';
    case Breeder    = 'breeder';
    case Sold       = 'sold';
    case NotForSale = 'not_for_sale';

    public function label(): string
    {
        return match ($this) {
            self::ForSale    => 'For Sale',
            self::Breeder    => 'Breeder',
            self::Sold       => 'Sold',
            self::NotForSale => 'Not For Sale',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::ForSale    => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
            self::Breeder    => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
            self::Sold       => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
            self::NotForSale => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
        };
    }

    public static function fromJsonState(string $state): ?self
    {
        return match ($state) {
            'For Sale'     => self::ForSale,
            'Breeder'      => self::Breeder,
            'Sold'         => self::Sold,
            'Not For Sale' => self::NotForSale,
            default        => null,
        };
    }
}
