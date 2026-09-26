<?php

use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Teacher\TeacherDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — SignVerse
|--------------------------------------------------------------------------
| Struktur:
|   - Public      : landing, auth (register/login/logout)
|   - /student/*  : area student (middleware role:student)
|   - /teacher/*  : area teacher (middleware role:teacher)
|   - shared      : profil (auth)
|
| Detail fitur per modul ditambahkan bertahap per phase.
*/

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::get('/', LandingController::class)->name('landing');

/*
|--------------------------------------------------------------------------
| Student area (role: student)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/dashboard', StudentDashboardController::class)
            ->name('dashboard');
    });

/*
|--------------------------------------------------------------------------
| Teacher area (role: teacher)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:teacher'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        Route::get('/dashboard', TeacherDashboardController::class)
            ->name('dashboard');
    });

/*
|--------------------------------------------------------------------------
| Authenticated shared routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
