<?php

namespace App\Policies;

use App\Models\JobPost;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class JobPostPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any job posts.
     */
    public function viewAny(User $user): bool
    {
        // Admin sees all; HR users see their own company's posts
        return $user->isAdmin() || $user->isHr();
    }

    /**
     * Determine whether the user can view a specific job post.
     */
    public function view(User $user, JobPost $jobPost): bool
    {
        return $user->isAdmin() || ($user->company && $user->company->id === $jobPost->company_id);
    }

    /**
     * Determine whether the user can create a job post.
     */
    public function create(User $user): bool
    {
        // Admin, HR, and company owners can create
        return $user->isAdmin() || $user->isHr() || ($user->company !== null);
    }

    /**
     * Determine whether the user can update a job post.
     */
    public function update(User $user, JobPost $jobPost): bool
    {
        return $user->isAdmin() || ($user->company && $user->company->id === $jobPost->company_id);
    }

    /**
     * Determine whether the user can delete a job post.
     */
    public function delete(User $user, JobPost $jobPost): bool
    {
        return $user->isAdmin() || ($user->company && $user->company->id === $jobPost->company_id);
    }

    /**
     * Determine whether the user can restore a soft‑deleted job post.
     */
    public function restore(User $user, JobPost $jobPost): bool
    {
        return $user->isAdmin() || ($user->company && $user->company->id === $jobPost->company_id);
    }

    /**
     * Determine whether the user can permanently delete a job post.
     */
    public function forceDelete(User $user, JobPost $jobPost): bool
    {
        return $user->isAdmin();
    }
}