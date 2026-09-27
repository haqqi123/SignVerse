<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/*
 * Auth controller — paritas dengan signlib/auth.py:
 * login via username (berformat email) + password, registrasi
 * selalu role student. redirect_after_login dipakai bila target
 * sesuai role (paritas redirect_after_login()).
 */
class AuthController extends Controller
{
    public function showLogin(): View
    {
        if (Auth::check()) {
            return $this->dashboardRedirect(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $credentials['username'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password_hash)) {
            return back()
                ->withErrors(['username' => 'Email atau password salah. Coba lagi.'])
                ->onlyInput('username');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return $this->afterLoginRedirect($user);
    }

    public function showRegister(): View
    {
        if (Auth::check()) {
            return $this->dashboardRedirect(Auth::user());
        }

        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'email', 'max:255', 'unique:users,username'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $user = User::create([
            'username' => strtolower($data['username']),
            'password_hash' => Hash::make($data['password']),
            'name' => trim($data['name']),
            'role' => 'student',
        ]);

        Auth::login($user);

        return $this->afterLoginRedirect($user)
            ->with('success', 'Akun berhasil dibuat. Selamat belajar!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /*
     * Paritas redirect_after_login(): hormati target tersimpan bila
     * sesuai role, selain itu kembali ke dashboard per role.
     */
    private function afterLoginRedirect(User $user): RedirectResponse
    {
        $target = session()->pull('redirect_after_login');

        if ($target && str_contains($target, '/'.$user->role.'/')) {
            return redirect()->to($target);
        }

        return $this->dashboardRedirect($user);
    }

    private function dashboardRedirect(User $user): RedirectResponse
    {
        return $user->isTeacher()
            ? redirect()->route('teacher.dashboard')
            : redirect()->route('student.dashboard');
    }
}
