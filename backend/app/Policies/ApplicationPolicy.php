<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Application $application): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isHr()) {
            $companyId = $user->company?->id;
            return $companyId && $application->jobPost->company_id === $companyId;
        }

        return $application->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isUser() || $user->role === 'user';
    }

    public function updateStatus(User $user, Application $application): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isHr()) {
            $companyId = $user->company?->id;
            return $companyId && $application->jobPost->company_id === $companyId;
        }

        return false;
    }

    public function withdraw(User $user, Application $application): bool
    {
        return $application->user_id === $user->id;
    }
}