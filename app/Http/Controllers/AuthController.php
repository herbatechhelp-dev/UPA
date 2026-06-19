<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    /**
     * Display the login view.
     */
    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Handle an authentication attempt.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Email atau password yang Anda masukkan salah.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();
        $user->load('role');

        // Redirect to the appropriate dashboard based on role
        return redirect($this->resolveDashboard($user));
    }

    /**
     * Destroy the authenticated session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Resolve dashboard URL based on user role slug.
     */
    private function resolveDashboard($user): string
    {
        return match ($user->role->slug ?? '') {
            'superadmin' => route('superadmin.dashboard'),
            'admin'      => route('admin.dashboard'),
            'ustad'      => route('ustad.dashboard'),
            'leader'     => route('leader.dashboard'),
            'member'     => route('member.dashboard'),
            default      => '/',
        };
    }
}
