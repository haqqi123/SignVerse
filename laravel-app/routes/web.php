<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

/*
 * Routing SignTeach Laravel — paritas build_navigation() di app.py.
 * L1: semua route halaman siswa & guru terdaftar; isinya dimigrasikan
 * bertahap (L2–L6) via view placeholder "coming in phase".
 */

// Landing (guest melihat halaman; user login di-redirect)
Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/go-practice', [LandingController::class, 'practiceTeaser'])->name('practice.teaser');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ── Siswa ─────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
    Route::get('/materials', [StudentController::class, 'materials'])->name('materials');
    Route::get('/practice', [StudentController::class, 'practice'])->name('practice');
    Route::post('/practice/submit', [StudentController::class, 'submitPractice'])->name('practice.submit');
    Route::get('/challenge', [StudentController::class, 'challenge'])->name('challenge');
    Route::get('/progress', [StudentController::class, 'progress'])->name('progress');
    Route::get('/achievements', [StudentController::class, 'achievements'])->name('achievements');
    Route::get('/assignment', [StudentController::class, 'assignment'])->name('assignment');
    Route::post('/assignment/{id}/start', [StudentController::class, 'startAssignment'])->name('assignment.start');
    Route::post('/assignment/{id}/complete', [StudentController::class, 'completeAssignment'])->name('assignment.complete');
    Route::get('/inclusive', [StudentController::class, 'inclusive'])->name('inclusive');
    Route::get('/profile', [StudentController::class, 'profile'])->name('profile');
    Route::post('/profile', [StudentController::class, 'updateProfile'])->name('profile.update');
});

// ── Guru ──────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [TeacherController::class, 'dashboard'])->name('dashboard');
    Route::get('/students', [TeacherController::class, 'students'])->name('students');
    Route::get('/monitoring', [TeacherController::class, 'monitoring'])->name('monitoring');
    Route::get('/assignments', [TeacherController::class, 'assignments'])->name('assignments');
    Route::post('/assignments', [TeacherController::class, 'createAssignment'])->name('assignments.create');
    Route::get('/reports', [TeacherController::class, 'reports'])->name('reports');
    Route::get('/reports/export', [TeacherController::class, 'exportReport'])->name('reports.export');
    Route::get('/profile', [TeacherController::class, 'profile'])->name('profile');
    Route::post('/profile', [TeacherController::class, 'updateProfile'])->name('profile.update');
});
