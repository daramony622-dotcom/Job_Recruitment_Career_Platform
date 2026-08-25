<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\GoogleAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('resend-otp', [AuthController::class, 'resendOtp']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);

Route::get('google', [GoogleAuthController::class, 'redirect']);

    Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);
});



Route::middleware(['auth:sanctum', 'role:admin'])
    ->prefix('admin')
    ->group(base_path('routes/admin.php'));

Route::middleware(['auth:sanctum', 'role:hr'])
    ->prefix('hr')
    ->group(base_path('routes/company.php'));

Route::middleware(['auth:sanctum', 'role:user'])
    ->prefix('user')
    ->group(base_path('routes/jobseeker.php'));