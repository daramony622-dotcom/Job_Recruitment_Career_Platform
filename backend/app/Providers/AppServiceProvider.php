<?php

namespace App\Providers;

use App\Models\JobPost;
use App\Policies\JobPostPolicy;
use App\Models\Company;
use App\Policies\ApplicationPolicy;
use App\Policies\CompanyPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\Application;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    protected $policies = [
        Application::class => ApplicationPolicy::class,
    ];
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(JobPost::class, JobPostPolicy::class);
        Gate::policy(Company::class, CompanyPolicy::class);
    }

}
