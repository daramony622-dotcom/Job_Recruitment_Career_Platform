<?php

use App\Http\Controllers\Api\Company\CompanyProfileController;
use App\Http\Controllers\Api\Company\JobPostController;
use App\Http\Controllers\Api\Company\ApplicantController;
use App\Http\Controllers\Api\Company\InterviewController;
use App\Http\Controllers\Api\Company\ReportController;
use Illuminate\Support\Facades\Route;


// Company profile (the logged-in HR's own company)
Route::get('profile', [CompanyProfileController::class, 'show']);
Route::post('profile', [CompanyProfileController::class, 'store']);
Route::put('profile', [CompanyProfileController::class, 'update']);

// Create, update, delete jobs
Route::apiResource('job-posts', JobPostController::class);

// View and search applicants
Route::get('applicants', [ApplicantController::class, 'index']);
Route::get('applicants/{application}', [ApplicantController::class, 'show']);

// Shortlist / reject candidates, update status
Route::patch('applicants/{application}/shortlist', [ApplicantController::class, 'shortlist']);
Route::patch('applicants/{application}/reject', [ApplicantController::class, 'reject']);
Route::patch('applicants/{application}/status', [ApplicantController::class, 'updateStatus']);

// Schedule interviews
Route::apiResource('interviews', InterviewController::class)->except(['destroy']);

// Recruitment reports
Route::get('reports', [ReportController::class, 'index']);