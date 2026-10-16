<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
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
| Web Routes SignVerse
|--------------------------------------------------------------------------
| Nome route yang dipakai konsisten lintas modul:
|   student.dashboard, student.materials.index/show, student.practice.*,
|   student.progress, student.challenge.show/attempt,
|   student.achievements.index, student.assignments.*,
|   teacher.dashboard, teacher.students.index/show, teacher.assignments.*
*/

// Landing page publik
Route::get('/', LandingController::class)->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');

// ── Area Student ─────────────────────────────────────────────────────
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', StudentDashboardController::class)->name('dashboard');
    Route::get('/materials', [MaterialController::class, 'index'])->name('materials.index');
    Route::get('/materials/{material}', [MaterialController::class, 'show'])->name('materials.show');

    Route::get('/practice', [PracticeController::class, 'index'])->name('practice.index');
    Route::get('/practice/start/{lesson}', [PracticeController::class, 'start'])->name('practice.start');
    Route::get('/practice/{session}', [PracticeController::class, 'show'])->name('practice.show');
    Route::post('/practice/{session}/attempt', [PracticeController::class, 'attempt'])->name('practice.attempt');
    Route::post('/practice/{session}/complete', [PracticeController::class, 'complete'])->name('practice.complete');
    Route::get('/practice/{session}/result', [PracticeController::class, 'result'])->name('practice.result');

    Route::get('/challenge', [ChallengeController::class, 'show'])->name('challenge');

    Route::get('/progress', ProgressController::class)->name('progress');
    Route::get('/achievements', [AchievementController::class, 'index'])->name('achievements.index');

    Route::get('/assignments', [StudentAssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/{assignment}', [StudentAssignmentController::class, 'show'])->name('assignments.show');
    Route::post('/assignments/{assignment}/submit', [StudentAssignmentController::class, 'submit'])->name('assignments.submit');
});

// ── Area Teacher ─────────────────────────────────────────────────────
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', TeacherDashboardController::class)->name('dashboard');
    Route::get('/students', [TeacherStudentController::class, 'index'])->name('students.index');
    Route::get('/students/{student}', [TeacherStudentController::class, 'show'])->name('students.show');

    Route::get('/assignments', [TeacherAssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/create', [TeacherAssignmentController::class, 'create'])->name('assignments.create');
    Route::post('/assignments', [TeacherAssignmentController::class, 'store'])->name('assignments.store');
    Route::get('/assignments/{assignment}', [TeacherAssignmentController::class, 'show'])->name('assignments.show');
    Route::delete('/assignments/{assignment}', [TeacherAssignmentController::class, 'destroy'])->name('assignments.destroy');
});

// ── Shared authenticated area ────────────────────────────────────────
Route::middleware(['auth', 'role:student,teacher'])->group(function () {
    Route::get('/verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('/confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/{path?}', fn () => redirect()->route('landing'))
    ->where('path', '^(?!.*api).*$')
    ->name('fallback');
