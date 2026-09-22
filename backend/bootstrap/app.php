<?php

use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors as BaseCors;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        /*
        |------------------------------------------------------------------
        | CORS — replace Laravel's built-in with our custom one
        |------------------------------------------------------------------
        */
        $middleware->remove(BaseCors::class);
        $middleware->prepend(\App\Http\Middleware\HandleCors::class);

        /*
        |------------------------------------------------------------------
        | CSRF Exception — Exclude API routes from CSRF verification
        |------------------------------------------------------------------
        */
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'api/auth/*',
            'auth/*',
        ]);

        /*
        |------------------------------------------------------------------
        | Guest redirects — API should return 401 JSON, not redirect
        |------------------------------------------------------------------
        */
        $middleware->redirectGuestsTo(function (Request $request) {
            return $request->is('api/*') ? null : route('login');
        });

        /*
        |------------------------------------------------------------------
        | Sanctum stateful API (enables SPA cookie auth)
        |------------------------------------------------------------------
        */
        $middleware->statefulApi();

        /*
        |------------------------------------------------------------------
        | Custom middleware aliases
        |------------------------------------------------------------------
        */
        $middleware->alias([
            'role'         => RoleMiddleware::class,
            'verified.otp' => AuthMiddleware::class,
            'has.company'  => \App\Http\Middleware\EnsureHasCompany::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Force JSON for API routes
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // 401 JSON for unauthenticated
        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Unauthenticated.',
                ], 401);
            }
        });
    })
    ->create();