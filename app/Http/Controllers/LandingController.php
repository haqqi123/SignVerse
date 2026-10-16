<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Landing page publik (tanpa login) — konsep dari project lama:
 * hero SignVerse, statistik platform dari DB, CTA login/register.
 * Statistik detail akan ditarik via service di phase berikutnya.
 */
class LandingController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('landing');
    }
}
