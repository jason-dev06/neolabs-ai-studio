<?php

namespace App\Enums;

enum VideoQualityTier: string
{
    case Fast = 'fast';
    case Standard = 'standard';

    public function baseCreditCost(): int
    {
        return match ($this) {
            self::Fast => 30,
            self::Standard => 60,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Fast => 'Veo 3.1 Fast',
            self::Standard => 'Veo 3.1',
        };
    }

    public function model(): string
    {
        return match ($this) {
            self::Fast => 'veo-3.1-fast-generate-preview',
            self::Standard => 'veo-3.1-generate-preview',
        };
    }

    public function sdkQuality(): string
    {
        return match ($this) {
            self::Fast => 'fast',
            self::Standard => 'standard',
        };
    }
}
