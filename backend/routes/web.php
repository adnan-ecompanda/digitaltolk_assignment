<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Provide a minimal named `login` route to avoid redirects when API auth middleware
// attempts to resolve `route('login')` during unauthenticated handling. The
// application uses token-based API auth; this route is only present to satisfy
// framework helpers and should not be used for interactive login in production.
Route::get('/login', function () {
    return response('Use API token authentication. See /api/v1/token', 200);
})->name('login');
