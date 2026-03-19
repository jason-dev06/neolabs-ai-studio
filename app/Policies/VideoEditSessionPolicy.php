<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VideoEditSession;

class VideoEditSessionPolicy
{
    public function view(User $user, VideoEditSession $session): bool
    {
        return $user->id === $session->user_id;
    }

    public function update(User $user, VideoEditSession $session): bool
    {
        return $user->id === $session->user_id;
    }

    public function delete(User $user, VideoEditSession $session): bool
    {
        return $user->id === $session->user_id;
    }
}
