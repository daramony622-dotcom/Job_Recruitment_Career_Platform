<?php

use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\CompanyController;
use App\Http\Controllers\Api\Admin\JobCategoryController;
use App\Http\Controllers\Api\Admin\JobPostController;
use App\Http\Controllers\Api\Admin\SkillController;
use App\Http\Controllers\Api\Admin\ApplicationController;
use App\Http\Controllers\Api\Admin\InterviewController;
use App\Http\Controllers\Api\Admin\ReportController;
use App\Http\Controllers\Api\Admin\SettingController;
use Illuminate\Support\Facades\Route;

// Manage users and companies
Route::apiResource('users', UserController::class);
Route::apiResource('companies', CompanyController::class);

// Manage job categories, jobs, and skills
Route::apiResource('job-categories', JobCategoryController::class);
Route::apiResource('job-posts', JobPostController::class);
Route::apiResource('skills', SkillController::class);

// View applications and interviews (read-only for admin)
Route::get('applications', [ApplicationController::class, 'index']);
Route::get('applications/{application}', [ApplicationController::class, 'show']);
Route::get('interviews', [InterviewController::class, 'index']);
Route::get('interviews/{interview}', [InterviewController::class, 'show']);

// System reports and settings
Route::get('reports', [ReportController::class, 'index']);
Route::get('settings', [SettingController::class, 'index']);
Route::put('settings', [SettingController::class, 'update']);