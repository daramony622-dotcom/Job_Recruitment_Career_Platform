<?php

namespace App\Policies;

use App\Models\SavedJob;
use App\Models\User;

class SavedJobPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SavedJob $savedJob): bool
    {
        return $user->isAdmin() || $user->id === $savedJob->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function delete(User $user, SavedJob $savedJob): bool
    {
        return $user->isAdmin() || $user->id === $savedJob->user_id;
    }
}
