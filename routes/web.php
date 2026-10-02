<?php

use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\AchievementController;
use App\Http\Controllers\Student\AssignmentController as StudentAssignmentController;
use App\Http\Controllers\Student\ChallengeController;
use App\Http\Controllers\Student\MaterialController;
use App\Http\Controllers\Student\PracticeController;
use App\Http\Controllers\Student\ProgressController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Teacher\AssignmentController as TeacherAssignmentController;
use App\Http\Controllers\Teacher\StudentController as TeacherStudentController;
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

        // Phase 3 — materi belajar & progress
        Route::get('/materials', [MaterialController::class, 'index'])->name('materials.index');
        Route::get('/materials/{material}', [MaterialController::class, 'show'])->name('materials.show');
        Route::get('/progress', ProgressController::class)->name('progress');

        // Phase 4 — AI practice
        Route::get('/practice', [PracticeController::class, 'index'])->name('practice.index');
        Route::get('/practice/{session}', [PracticeController::class, 'show'])
            ->name('practice.show')->whereNumber('session');
        Route::post('/practice/{session}/attempt', [PracticeController::class, 'attempt'])
            ->name('practice.attempt')->whereNumber('session');
        Route::post('/practice/{session}/complete', [PracticeController::class, 'complete'])
            ->name('practice.complete')->whereNumber('session');
        Route::get('/practice/{session}/result', [PracticeController::class, 'result'])
            ->name('practice.result')->whereNumber('session');
        Route::get('/practice/lesson/{lesson}/start', [PracticeController::class, 'start'])
            ->name('practice.start');

        // Phase 5 — gamification
        Route::get('/challenge', [ChallengeController::class, 'show'])->name('challenge.show');
        Route::post('/challenge/attempt', [ChallengeController::class, 'attempt'])->name('challenge.attempt');
        Route::get('/achievements', [AchievementController::class, 'index'])->name('achievements.index');

        // Phase 6 — assignment (student)
        Route::get('/assignments', [StudentAssignmentController::class, 'index'])->name('assignments.index');
        Route::get('/assignments/{assignment}', [StudentAssignmentController::class, 'show'])
            ->name('assignments.show')->whereNumber('assignment');
        Route::post('/assignments/{assignment}/submit', [StudentAssignmentController::class, 'submit'])
            ->name('assignments.submit')->whereNumber('assignment');
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

        // Phase 7 — monitoring siswa
        Route::get('/students', [TeacherStudentController::class, 'index'])->name('students.index');
        Route::get('/students/{student}', [TeacherStudentController::class, 'show'])
            ->name('students.show')->whereNumber('student');

        // Phase 6 — assignment (teacher)
        Route::get('/assignments', [TeacherAssignmentController::class, 'index'])->name('assignments.index');
        Route::get('/assignments/create', [TeacherAssignmentController::class, 'create'])->name('assignments.create');
        Route::post('/assignments', [TeacherAssignmentController::class, 'store'])->name('assignments.store');
        Route::get('/assignments/{assignment}', [TeacherAssignmentController::class, 'show'])
            ->name('assignments.show')->whereNumber('assignment');
        Route::delete('/assignments/{assignment}', [TeacherAssignmentController::class, 'destroy'])
            ->name('assignments.destroy')->whereNumber('assignment');
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
