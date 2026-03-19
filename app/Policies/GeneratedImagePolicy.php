<?php

namespace App\Policies;

use App\Models\GeneratedImage;
use App\Models\User;

class GeneratedImagePolicy
{
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GeneratedImage $generatedImage): bool
    {
        return $user->id === $generatedImage->user_id;
    }
}
