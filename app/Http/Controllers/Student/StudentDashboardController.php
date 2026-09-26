<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Dashboard student — Phase 1 menampilkan profil & role.
 * Statistik pembelajaran (XP, streak, progress) akan ditambahkan
 * di Phase 3 via StudentDashboardService.
 */
class StudentDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('student.dashboard', [
            'user' => $user,
        ]);
    }
}
