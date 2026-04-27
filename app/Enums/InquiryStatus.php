<?php

namespace App\Enums;

enum InquiryStatus: string
{
    case New     = 'new';
    case Read    = 'read';
    case Replied = 'replied';
    case Closed  = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::New     => 'New',
            self::Read    => 'Read',
            self::Replied => 'Replied',
            self::Closed  => 'Closed',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::New     => 'bg-orange-500 text-white',
            self::Read    => 'bg-blue-500 text-white',
            self::Replied => 'bg-green-600 text-white',
            self::Closed  => 'bg-gray-400 text-white',
        };
    }
}
