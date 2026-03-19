<?php

namespace App\Enums;

enum VideoDuration: string
{
    case FourSeconds = '4';
    case SixSeconds = '6';
    case EightSeconds = '8';

    public function seconds(): int
    {
        return (int) $this->value;
    }

    public function label(): string
    {
        return match ($this) {
            self::FourSeconds => '4s',
            self::SixSeconds => '6s',
            self::EightSeconds => '8s',
        };
    }

    public function creditMultiplier(): float
    {
        return match ($this) {
            self::FourSeconds => 1.0,
            self::SixSeconds => 1.5,
            self::EightSeconds => 2.0,
        };
    }
}
