<?php

namespace App\Policies;

use App\Models\Interview;
use App\Models\User;

class InterviewPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Scoped by role inside InterviewService / Controller
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Interview $interview): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($interview->applicant_id === $user->id || $interview->interviewer_id === $user->id) {
            return true;
        }

        if ($user->company && $interview->job && $interview->job->company_id === $user->company->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasRole('hr') || $user->hasRole('company');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Interview $interview): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($interview->interviewer_id === $user->id) {
            return true;
        }

        if ($user->company && $interview->job && $interview->job->company_id === $user->company->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Interview $interview): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($interview->interviewer_id === $user->id) {
            return true;
        }

        if ($user->company && $interview->job && $interview->job->company_id === $user->company->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Interview $interview): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Interview $interview): bool
    {
        return $user->isAdmin();
    }
}
