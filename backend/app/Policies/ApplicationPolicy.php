<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function viewAny(): bool
    {
        return true;
    }

    public function view(User $user, Application $application): bool
    {
        if ($user->isAdmin() || $user->isHr()) {
            return true;
        }

        return $application->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function updateStatus(User $user, Application $application): bool
    {
        if ($user->isAdmin() || $user->isHr()) {
            return true;
        }

        return false;
    }

    public function withdraw(User $user, Application $application): bool
    {
        return $application->user_id === $user->id;
    }
}
