<?php

use App\Http\Controllers\Api\JobSeeker\ProfileController;
use App\Http\Controllers\Api\JobSeeker\EducationController;
use App\Http\Controllers\Api\JobSeeker\ExperienceController;
use App\Http\Controllers\Api\JobSeeker\CVController;
use App\Http\Controllers\Api\JobSeeker\SkillController;
use App\Http\Controllers\Api\JobSeeker\JobSearchController;
use App\Http\Controllers\Api\JobSeeker\SavedJobController;
use App\Http\Controllers\Api\JobSeeker\ApplicationController;
use App\Http\Controllers\Api\JobSeeker\NotificationController;
use Illuminate\Support\Facades\Route;


// Profile
Route::get('profile', [ProfileController::class, 'show']);
Route::put('profile', [ProfileController::class, 'update']);

// Education & experience
Route::apiResource('education', EducationController::class);
Route::apiResource('experience', ExperienceController::class);

// Skills (attach/detach from user_skill pivot)
Route::get('skills', [SkillController::class, 'index']);
Route::post('skills', [SkillController::class, 'store']);
Route::delete('skills/{skill}', [SkillController::class, 'destroy']);

// CV upload
Route::post('cv', [CVController::class, 'store']);
Route::delete('cv', [CVController::class, 'destroy']);

// Search / filter jobs
Route::get('jobs/search', [JobSearchController::class, 'index']);

// Saved jobs
Route::get('saved-jobs', [SavedJobController::class, 'index']);
Route::post('saved-jobs/{jobPost}', [SavedJobController::class, 'store']);
Route::delete('saved-jobs/{jobPost}', [SavedJobController::class, 'destroy']);

// Apply & application history/status
Route::get('applications', [ApplicationController::class, 'index']);
Route::post('applications', [ApplicationController::class, 'store']);
Route::get('applications/{application}', [ApplicationController::class, 'show']);

// Notifications
Route::get('notifications', [NotificationController::class, 'index']);
Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);