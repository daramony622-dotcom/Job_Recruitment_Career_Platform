<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\TelegramAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authenticated User Endpoint
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Auth Endpoints
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('resend-otp', [AuthController::class, 'resendOtp']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::get('google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::match(['get', 'post'], 'google/callback', [GoogleAuthController::class, 'callback']);
    Route::get('telegram', [TelegramAuthController::class, 'redirect'])->name('auth.telegram');
    Route::match(['get', 'post'], 'telegram/callback', [TelegramAuthController::class, 'login'])->name('auth.telegram.callback');
    Route::middleware('auth:sanctum')->post('telegram/email', [TelegramAuthController::class, 'addEmail']);

    Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);
});

// Admin Routes File Loader (Prefix & Middleware handled here)
Route::middleware(['auth:sanctum', 'role:admin'])
    ->prefix('admin')
    ->group(base_path('routes/admin.php'));

// Company/HR Routes File Loader
Route::middleware(['auth:sanctum', 'role:hr,company,admin'])
    ->prefix('company')
    ->group(base_path('routes/company.php'));

// Jobseeker/User Routes File Loader
Route::middleware(['auth:sanctum', 'role:user'])
    ->prefix('user')
    ->group(base_path('routes/jobseeker.php'));