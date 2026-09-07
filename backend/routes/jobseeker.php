<?php

use App\Http\Controllers\Api\JobSeeker\ApplicationController;
use App\Http\Controllers\Api\JobSeeker\CVController;
use App\Http\Controllers\Api\JobSeeker\EducationController;
use App\Http\Controllers\Api\JobSeeker\ExperienceController;
use App\Http\Controllers\Api\JobSeeker\InterviewController;
use App\Http\Controllers\Api\JobSeeker\JobSearchController;
use App\Http\Controllers\Api\JobSeeker\NotificationController;
use App\Http\Controllers\Api\JobSeeker\ProfileController;
use App\Http\Controllers\Api\JobSeeker\SavedJobController;
use App\Http\Controllers\Api\JobSeeker\SkillController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Job Seeker API Routes
|--------------------------------------------------------------------------
| Base Prefix (/api/user OR /api/job-seeker) & Middleware (auth:sanctum, role:user)
| are inherited from api.php
*/

// Profile
Route::get('profile', [ProfileController::class, 'show']);
Route::post('profile', [ProfileController::class, 'update']);
Route::put('profile', [ProfileController::class, 'update']);

// Education & experience
Route::apiResource('education', EducationController::class);
Route::apiResource('experience', ExperienceController::class);


// Skills (attach/detach from user_skill pivot)
Route::get('skills', [SkillController::class, 'index']);
Route::post('skills', [SkillController::class, 'store']);
Route::delete('skills/{skill}', [SkillController::class, 'destroy']);

// CV management
Route::apiResource('cv', CVController::class)->only([
	'index', 'store', 'show', 'update', 'destroy',
]);

// Search / filter jobs
Route::get('jobs/search', [JobSearchController::class, 'index']);

// Saved jobs
Route::get('saved-jobs', [SavedJobController::class, 'index']);
Route::post('saved-jobs', [SavedJobController::class, 'store']);
Route::delete('saved-jobs/{savedJob}', [SavedJobController::class, 'destroy']);


// Apply & application history/status
Route::get('applications', [ApplicationController::class, 'index']);
Route::post('applications', [ApplicationController::class, 'store']);
Route::get('applications/{application}', [ApplicationController::class, 'show']);
Route::patch('applications/{application}/withdraw', [ApplicationController::class, 'withdraw']);

// Interviews (read-only for job seekers — scheduled by company)
Route::get('interviews', [InterviewController::class, 'index']);
Route::get('interviews/{interview}', [InterviewController::class, 'show']);

// Notifications
Route::get('notifications', [NotificationController::class, 'index']);
Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
