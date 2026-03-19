<?php

namespace App\Policies;

use App\Models\GeneratedVideo;
use App\Models\User;

class GeneratedVideoPolicy
{
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GeneratedVideo $generatedVideo): bool
    {
        return $user->id === $generatedVideo->user_id;
    }
}
