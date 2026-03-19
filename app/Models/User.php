<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'credits'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function generatedImages(): HasMany
    {
        return $this->hasMany(GeneratedImage::class);
    }

    public function generatedVideos(): HasMany
    {
        return $this->hasMany(GeneratedVideo::class);
    }

    public function imageEditSessions(): HasMany
    {
        return $this->hasMany(ImageEditSession::class);
    }

    public function videoEditSessions(): HasMany
    {
        return $this->hasMany(VideoEditSession::class);
    }

    public function hasCredits(int $amount): bool
    {
        return $this->credits >= $amount;
    }

    public function deductCredits(int $amount): void
    {
        $this->decrement('credits', $amount);
    }
}
