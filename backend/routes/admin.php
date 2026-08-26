<?php

use App\Http\Controllers\Api\Admin\ApplicationController;
use App\Http\Controllers\Api\Admin\CompanyController;
use App\Http\Controllers\Api\Admin\InterviewController;
use App\Http\Controllers\Api\Admin\JobCategoryController;
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
    Route::apiResource('skills', SkillController::class);

    // View Applications & Interviews (Read-Only)
    Route::apiResource('applications', ApplicationController::class)->only(['index', 'show']);
    Route::apiResource('interviews', InterviewController::class)->only(['index', 'show']);

    // Reports & Settings
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
});