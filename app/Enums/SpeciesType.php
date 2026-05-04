<?php

namespace App\Enums;

enum SpeciesType: string
{
    case Syntype   = 'x';
    case Holotype  = 'h';
    case Lost      = 'o';
    case Paratype  = 'p';
    case Lectotype = 'l';
    case Neotype   = 'n';

    public function label(): string
    {
        return match($this) {
            self::Syntype   => 'Syntype',
            self::Holotype  => 'Holotype',
            self::Lost      => 'Lost/None',
            self::Paratype  => 'Paratype',
            self::Lectotype => 'Lectotype',
            self::Neotype   => 'Neotype',
        };
    }
}
