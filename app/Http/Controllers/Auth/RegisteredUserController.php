<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AssignmentService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * Registrasi publik hanya untuk role student (teacher dibuat via seeder
     * oleh admin, sesuai behavior project lama). Setelah registrasi user tidak
     * di-login otomatis, melainkan diarahkan ke form login.
     *
     * @throws ValidationException
     */
    public function store(Request $request, AssignmentService $assignments): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($request, $assignments) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => User::ROLE_STUDENT,
            ]);

            // Profil student (1:1) — data streak/gamification hidup di sini.
            $student = $user->student()->create();

            // Tugas guru yang masih aktif ikut dibagikan ke siswa baru.
            $assignments->distributeActiveTo($student);

            return $user;
        });

        event(new Registered($user));

        return redirect(route('login', absolute: false))
            ->with('status', 'Registrasi berhasil! Silakan masuk dengan akun yang baru dibuat.');
    }
}
