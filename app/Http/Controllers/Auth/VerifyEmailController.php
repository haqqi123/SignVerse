<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     *
     * Implementasi Laravel 12 (trait VerifiesEmails sudah dihapus
     * dari framework) dengan redirect ke dashboard sesuai role.
     *
     * @throws AuthorizationException
     */
    public function __invoke(Request $request): RedirectResponse
    {
        if (! hash_equals((string) $request->user()->getKey(), (string) $request->route('id'))) {
            throw new AuthorizationException;
        }

        if (! hash_equals(sha1($request->user()->getEmailForVerification()), (string) $request->route('hash'))) {
            throw new AuthorizationException;
        }

        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended($request->user()->dashboardRoute().'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended($request->user()->dashboardRoute().'?verified=1');
    }
}
