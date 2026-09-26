<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Dashboard teacher — Phase 1 menampilkan profil & role.
 * Statistik kelas (total students, avg score, weekly sessions)
 * akan ditambahkan di Phase 7 via service layer.
 */
class TeacherDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('teacher.dashboard', [
            'user' => $user,
        ]);
    }
}
