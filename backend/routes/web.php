<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This application is an API-only backend. The SPA frontend (Vue) is
| served separately (e.g. npm run dev / build). Web routes here are
| minimal — just a health-check and optional Google OAuth redirect landing.
|
*/

// Health-check / landing
Route::get('/', function () {
    return response()->json([
        'service' => 'Job Recruitment & Career Platform API',
        'status'  => 'running',
    ]);
});
