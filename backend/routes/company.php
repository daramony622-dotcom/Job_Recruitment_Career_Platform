<?php

namespace App\Http\Controllers\Api\Company;

use App\Http\Controllers\Api\Company\ApplicantController;
use App\Http\Controllers\Api\Company\CompanyProfileController;
use App\Http\Controllers\Api\Company\InterviewController;
use App\Http\Controllers\Api\Company\JobPostController;
use App\Http\Controllers\Api\Company\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Company API Routes
|--------------------------------------------------------------------------
| Base Prefix (/api/company) & Middleware (auth:sanctum, role:hr,company)
| are inherited automatically from api.php
*/

// Company Profile
Route::get('profile', [CompanyProfileController::class, 'show']);
Route::post('profile', [CompanyProfileController::class, 'store']);
Route::put('profile', [CompanyProfileController::class, 'update']);

// Routes requiring an associated company profile
Route::middleware('has.company')->group(function () {
    // Job Posts
    Route::apiResource('job-posts', JobPostController::class);
    Route::post('job-posts/{id}/restore', [JobPostController::class, 'restore'])
        ->name('company.job-posts.restore');
    Route::post('job-posts/{jobPost}/toggle-featured', [JobPostController::class, 'toggleFeatured'])
        ->name('company.job-posts.toggle-featured');

    // Applicants Management
    Route::get('applicants', [ApplicantController::class, 'index']);
    Route::get('applicants/{application}', [ApplicantController::class, 'show']);
    Route::patch('applicants/{application}/shortlist', [ApplicantController::class, 'shortlist']);
    Route::patch('applicants/{application}/reject', [ApplicantController::class, 'reject']);
    Route::patch('applicants/{application}/status', [ApplicantController::class, 'updateStatus']);

    // Interviews
    Route::apiResource('interviews', InterviewController::class)->except(['destroy']);

    // Reports
    Route::get('reports', [ReportController::class, 'index']);
});
