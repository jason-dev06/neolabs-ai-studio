<?php

namespace App\Enums;

enum VideoStyle: string
{
    case Cinematic = 'cinematic';
    case Anime = 'anime';
    case Documentary = 'documentary';
    case Commercial = 'commercial';
    case MusicVideo = 'music_video';
    case Vlog = 'vlog';

    public function label(): string
    {
        return match ($this) {
            self::Cinematic => 'Cinematic',
            self::Anime => 'Anime',
            self::Documentary => 'Documentary',
            self::Commercial => 'Commercial',
            self::MusicVideo => 'Music Video',
            self::Vlog => 'Vlog',
        };
    }

    public function sdkStyle(): string
    {
        return $this->value;
    }
}
