<?php

namespace App\Policies;

use App\Models\ImageEditSession;
use App\Models\User;

class ImageEditSessionPolicy
{
    public function view(User $user, ImageEditSession $session): bool
    {
        return $user->id === $session->user_id;
    }

    public function update(User $user, ImageEditSession $session): bool
    {
        return $user->id === $session->user_id;
    }

    public function delete(User $user, ImageEditSession $session): bool
    {
        return $user->id === $session->user_id;
    }
}
