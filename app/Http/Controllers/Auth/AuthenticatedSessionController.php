<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // If the login form included agreement to privacy/terms, persist it on the user
        try {
            $user = $request->user();
            if ($user && $request->boolean('agreed_to_terms')) {
                $user->agreed_to_terms = true;
                $user->agreed_at = now();
                $user->save();
            }
        } catch (\Throwable $e) {
            // don't block login on a save error; log and continue
            logger()->warning('Failed to persist agreed_to_terms on login: '.$e->getMessage());
        }

        // Always land on dashboard; skip any intended/dashboard fallback
        return redirect()->route('dashboard')->with('success', 'Logged in');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
