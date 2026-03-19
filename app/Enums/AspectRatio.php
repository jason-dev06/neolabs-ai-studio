<?php

namespace App\Enums;

enum AspectRatio: string
{
    case Square = '1:1';
    case Landscape16x9 = '16:9';
    case Portrait9x16 = '9:16';
    case Landscape4x3 = '4:3';
    case Portrait3x4 = '3:4';

    public function sdkOrientation(): string
    {
        return match ($this) {
            self::Square => 'square',
            self::Landscape16x9, self::Landscape4x3 => 'landscape',
            self::Portrait9x16, self::Portrait3x4 => 'portrait',
        };
    }
}
