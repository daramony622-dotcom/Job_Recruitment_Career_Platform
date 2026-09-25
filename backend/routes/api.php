<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\JobSeeker\JobSearchController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\TelegramAuthController;
use App\Http\Controllers\Auth\TelegramWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CSRF Token
|--------------------------------------------------------------------------
|
| This endpoint is only needed if you use Laravel's SPA/cookie-based
| authentication. Your current Vue app uses Bearer tokens, so it is
| not required for normal login.
|
*/

Route::get('/csrf-token', function () {
    return response()->json([
        'csrf_token' => csrf_token(),
    ]);
});

/*
|--------------------------------------------------------------------------
| Authenticated User
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')
    ->get('/user', function (Request $request) {
        return response()->json([
            'data' => $request->user(),
        ]);
    });

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | GET /auth/login
    |--------------------------------------------------------------------------
    |
    | This route is only useful if a browser visits the login URL directly.
    | The actual login request from Vue is POST /auth/login.
    |
    */

    Route::get('/login', function (Request $request) {
        if (
            $request->wantsJson() ||
            $request->expectsJson()
        ) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $frontendUrl = env(
            'FRONTEND_URL',
            'http://localhost:5174'
        );

        return redirect(
            $frontendUrl . '/login'
        );
    })->name('login');

    /*
    |--------------------------------------------------------------------------
    | Register
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/register',
        [AuthController::class, 'register']
    );

    /*
    |--------------------------------------------------------------------------
    | Email OTP
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/verify-otp',
        [AuthController::class, 'verifyOtp']
    );

    Route::post(
        '/resend-otp',
        [AuthController::class, 'resendOtp']
    );

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/login',
        [AuthController::class, 'login']
    );

    /*
    |--------------------------------------------------------------------------
    | Password
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/forgot-password',
        [AuthController::class, 'forgotPassword']
    );

    Route::post(
        '/reset-password',
        [AuthController::class, 'resetPassword']
    );

    /*
    |--------------------------------------------------------------------------
    | Google OAuth
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/google',
        [GoogleAuthController::class, 'redirect']
    )->name('auth.google');

    Route::match(
        ['get', 'post'],
        '/google/callback',
        [GoogleAuthController::class, 'callback']
    );

    /*
    |--------------------------------------------------------------------------
    | Telegram Authentication
    |--------------------------------------------------------------------------
    */

    Route::post('/telegram/init', [TelegramAuthController::class, 'initApi']);
    Route::get('/telegram/status/{token}', [TelegramAuthController::class, 'statusApi']);

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')
        ->post(
            '/logout',
            [AuthController::class, 'logout']
        );
});

/*
|--------------------------------------------------------------------------
| Telegram Bot Webhook
|--------------------------------------------------------------------------
|
| Telegram calls this endpoint directly.
|
*/


Route::post('/telegram/webhook', [TelegramWebhookController::class, '__invoke']);
/*
|--------------------------------------------------------------------------
| Public Browsing Routes
|--------------------------------------------------------------------------
*/

/*
| Job search
*/

Route::get(
    '/jobs/search',
    [JobSearchController::class, 'index']
);

Route::get(
    '/jobs/{jobPost}',
    [JobSearchController::class, 'show']
);

/*
| Categories
*/

Route::get(
    '/categories',
    [CategoryController::class, 'index']
);

Route::get(
    '/skill-categories',
    [CategoryController::class, 'index']
);

Route::get(
    '/skills',
    [CategoryController::class, 'skills']
);

/*
| Companies
*/

Route::get(
    '/companies',
    [CompanyController::class, 'index']
);

Route::get(
    '/companies/{company}',
    [CompanyController::class, 'show']
);

/*
| Contact
*/

Route::middleware('auth:sanctum')
    ->post(
        '/contact',
        [ContactController::class, 'submit']
    );

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth:sanctum',
    'role:admin',
])
    ->prefix('admin')
    ->group(
        base_path('routes/admin.php')
    );

/*
|--------------------------------------------------------------------------
| Company / HR Routes
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth:sanctum',
    'role:hr,company',
])
    ->prefix('company')
    ->group(
        base_path('routes/company.php')
    );

/*
|--------------------------------------------------------------------------
| Job Seeker / User Routes
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth:sanctum',
    'role:user,job_seeker,hr,company,admin',
])
    ->prefix('user')
    ->group(
        base_path('routes/jobseeker.php')
    );
