<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');


// Guest routes (only accessible when not logged in)
Route::middleware(['guest'])->group(function () {
    // Show login/register forms
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('show.login');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('show.register');

    // Handle form submissions
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
});

// Authenticated routes (only accessible when logged in)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard', [
            'user' => Auth::user()
        ]);
    })->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
