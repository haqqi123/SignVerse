<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/*
 * Landing page — paritas ui_pages/landing.py:
 * user login diarahkan ke dashboard sesuai role; guest melihat hero,
 * statistik platform dari DB, fitur, cara kerja, testimoni, footer.
 * CTA "Coba AI Practice" menyimpan redirect_after_login (paritas
 * st.session_state['redirect_after_login']).
 */
class LandingController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (Auth::check()) {
            return Auth::user()->isTeacher()
                ? redirect()->route('teacher.dashboard')
                : redirect()->route('student.dashboard');
        }

        $stats = [
            'students' => \App\Models\User::where('role', 'student')->count(),
            'weeklySessions' => \DB::table('practice_sessions')
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
            'materials' => \App\Models\Material::count(),
        ];

        return view('landing', ['stats' => $stats]);
    }

    /*
     * CTA "Coba AI Practice" (guest) — paritas _goto_practice_after_login():
     * setelah login siswa, langsung diarahkan ke halaman AI Practice.
     */
    public function practiceTeaser(): RedirectResponse
    {
        session(['redirect_after_login' => route('student.practice')]);

        return redirect()->route('login');
    }
}
