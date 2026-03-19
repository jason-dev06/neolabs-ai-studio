<?php

namespace App\Enums;

enum QualityTier: string
{
    case Basic = 'basic';
    case Smart = 'smart';
    case Genius = 'genius';

    public function creditCost(): int
    {
        return match ($this) {
            self::Basic => 10,
            self::Smart => 25,
            self::Genius => 50,
        };
    }

    public function sdkQuality(): string
    {
        return match ($this) {
            self::Basic => 'low',
            self::Smart => 'medium',
            self::Genius => 'high',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Basic => 'Basic',
            self::Smart => 'Smart',
            self::Genius => 'Genius',
        };
    }
}
