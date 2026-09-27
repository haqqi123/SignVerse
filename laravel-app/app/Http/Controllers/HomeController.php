<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user) {
            return $user->isTeacher()
                ? redirect()->route('teacher.dashboard')
                : redirect()->route('student.dashboard');
        }

        return view('landing');
    }
}
