<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\TelegramAuthController;
use App\Http\Controllers\Auth\TelegramWebhookController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\JobSeeker\JobSearchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// Removed: use Telegram\Bot\Laravel\Facades\Telegram;
// This was the irazasyed/telegram-bot-sdk facade, unused here, and easily
// confused with our own App\Services\Telegram used by the controllers below.

/*
|--------------------------------------------------------------------------
| CSRF Token (for SPA frontend)
|--------------------------------------------------------------------------
*/
Route::get('/csrf-token', fn (Request $request) => response()->json([
    'csrf_token' => csrf_token(),
]));

/*
|--------------------------------------------------------------------------
| Authenticated User
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->get('/user', fn (Request $request) => $request->user());

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {

    Route::get('login', function (Request $request) {
        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
        return redirect($frontendUrl . '/login');
    })->name('login');

    Route::post('register',        [AuthController::class, 'register']);
    Route::post('verify-otp',      [AuthController::class, 'verifyOtp']);
    Route::post('resend-otp',      [AuthController::class, 'resendOtp']);
    Route::post('login',           [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password',  [AuthController::class, 'resetPassword']);

    // Google OAuth
    Route::get('google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::match(['get', 'post'], 'google/callback', [GoogleAuthController::class, 'callback']);

    // Telegram — bot deep-link auth. One flow serves both login and register:
    // the webhook creates the user on first confirmation and reuses it on every one after.
    Route::prefix('telegram')->group(function () {
        Route::post('init', [TelegramAuthController::class, 'init'])
            ->middleware('throttle:10,1')
            ->name('telegram.init');

        Route::get('status/{token}', [TelegramAuthController::class, 'status'])
            ->middleware('throttle:120,1')
            ->where('token', '[A-Za-z0-9]{40}')
            ->name('telegram.status');

        // Official Telegram Login Widget flow — independent of init/status above.
        Route::match(['get', 'post'], 'callback', [TelegramAuthController::class, 'callback'])
            ->middleware('throttle:30,1')
            ->name('telegram.callback');
    });

    // Logout (protected)
    Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);

});


/*
|--------------------------------------------------------------------------
| Telegram Bot Webhook
|--------------------------------------------------------------------------
| Called by Telegram's servers, not the frontend. Kept outside /auth so it
| reads clearly as server-to-server, and verified via a secret header
| (config('services.telegram.webhook_secret')) rather than any user-facing
| auth guard. Throttled to cap abuse before the header check even runs.
*/
Route::post('telegram/webhook', [TelegramWebhookController::class, 'handle'])
    ->middleware('throttle:60,1')
    ->name('telegram.webhook');


/*
|--------------------------------------------------------------------------
| Public Browsing Routes
|--------------------------------------------------------------------------
*/
Route::get('jobs/search',           [JobSearchController::class, 'index']);
Route::get('jobs/{jobPost}',        [JobSearchController::class, 'show']);
Route::get('categories',            [CategoryController::class, 'index']);
Route::get('skill-categories',      [CategoryController::class, 'index']);
Route::get('skills',                [CategoryController::class, 'skills']);
Route::get('companies',             [CompanyController::class, 'index']);
Route::get('companies/{company}',   [CompanyController::class, 'show']);

Route::middleware('auth:sanctum')->post('contact', [ContactController::class, 'submit']);

/*
|--------------------------------------------------------------------------
| Role-protected Route Files
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'role:admin,hr,company'])
    ->prefix('admin')
    ->group(base_path('routes/admin.php'));

Route::middleware(['auth:sanctum', 'role:hr,company,admin'])
    ->prefix('company')
    ->group(base_path('routes/company.php'));

Route::middleware(['auth:sanctum', 'role:user,job_seeker,hr,company,admin'])
    ->prefix('user')
    ->group(base_path('routes/jobseeker.php'));