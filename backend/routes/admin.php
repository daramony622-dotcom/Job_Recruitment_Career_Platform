<?php

use App\Http\Controllers\Api\Admin\ApplicationController;
use App\Http\Controllers\Api\Admin\CompanyController;
use App\Http\Controllers\Api\Admin\FailedJobController;
use App\Http\Controllers\Api\Admin\InterviewController;
use App\Http\Controllers\Api\Admin\JobBatchController;
use App\Http\Controllers\Api\Admin\JobCategoryController;
use App\Http\Controllers\Api\Admin\JobController;
use App\Http\Controllers\Api\Admin\JobPostController;
use App\Http\Controllers\Api\Admin\ReportController;
use App\Http\Controllers\Api\Admin\SettingController;
use App\Http\Controllers\Api\Admin\SkillController;
use App\Http\Controllers\Api\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
| These routes are loaded with the 'api/admin' prefix and authenticated
| via middleware (e.g., auth:sanctum, role:admin).
*/

Route::name('admin.')->group(function () {
    // Manage Users & Companies
    Route::apiResource('users', UserController::class);
    Route::apiResource('companies', CompanyController::class);
    Route::match(['put', 'patch'], 'companies/{company}/status', [CompanyController::class, 'updateStatus'])->name('companies.updateStatus');

    // Manage Job Categories, Posts, & Skills
    Route::apiResource('job-categories', JobCategoryController::class);
    Route::apiResource('job-posts', JobPostController::class);
    Route::post('job-posts/{id}/restore', [JobPostController::class, 'restore'])
        ->name('job-posts.restore');
    Route::delete('job-posts/{id}/force-delete', [JobPostController::class, 'forceDelete'])
        ->name('job-posts.forceDelete');
    Route::post('job-posts/{jobPost}/toggle-featured', [JobPostController::class, 'toggleFeatured'])
        ->name('job-posts.toggle-featured');
    Route::apiResource('skills', SkillController::class);

    // Queue Jobs Management (jobs, job-batches, failed-jobs)
    Route::apiResource('jobs', JobController::class);
    Route::apiResource('job-batches', JobBatchController::class);
    Route::post('failed-jobs/{failedJob}/retry', [FailedJobController::class, 'retry'])->name('failed-jobs.retry');
    Route::delete('failed-jobs/flush', [FailedJobController::class, 'flush'])->name('failed-jobs.flush');
    Route::apiResource('failed-jobs', FailedJobController::class);

    // View Applications & Interviews (Read-Only)
    Route::apiResource('applications', ApplicationController::class)->only(['index', 'show']);
    Route::apiResource('interviews', InterviewController::class)->only(['index', 'show']);

    // Reports & Settings
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
});