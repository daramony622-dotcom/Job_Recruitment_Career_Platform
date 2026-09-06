<?php

namespace App\Policies;

use App\Models\Education;
use App\Models\User;

class EducationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Education $education): bool
    {
        return $user->isAdmin() || $user->id === $education->user_id || $user->id === $education->profile?->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Education $education): bool
    {
        return $user->isAdmin() || $user->id === $education->user_id || $user->id === $education->profile?->user_id;
    }

    public function delete(User $user, Education $education): bool
    {
        return $user->isAdmin() || $user->id === $education->user_id || $user->id === $education->profile?->user_id;
    }

    public function restore(User $user, Education $education): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Education $education): bool
    {
        return $user->isAdmin();
    }
}
