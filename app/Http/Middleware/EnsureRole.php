<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guard role untuk area /student/* dan /teacher/*.
 *
 * - Guest      → redirect ke login (dengan intended URL).
 * - Role salah → redirect ke dashboard role-nya sendiri + flash error.
 *   Student tidak bisa membuka /teacher/* dan sebaliknya.
 */
class EnsureRole
{
    public const ROLE_STUDENT = 'student';
    public const ROLE_TEACHER = 'teacher';

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login')
                ->with('error', 'Silakan masuk terlebih dahulu.')
                ->withInput(['intended' => $request->fullUrl()]);
        }

        if (! in_array($user->role, $roles, true)) {
            return redirect($user->dashboardRoute())
                ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }

        return $next($request);
    }
}
