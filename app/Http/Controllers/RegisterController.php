<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    /**
     * Display the registration view.
     */
    public function showRegister(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle a registration request.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone'                 => ['required', 'string', 'max:20'],
            'gender'                => ['required', 'in:ikhwan,akhwat'],
            'department'            => ['required', 'string', 'max:255'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'phone'      => $validated['phone'],
            'gender'     => $validated['gender'],
            'department' => $validated['department'],
            'password'   => Hash::make($validated['password']),
            'role_id'    => null,
            'status'     => 'pending',
        ]);

        return redirect()->route('login')->with('success', 'Pendaftaran berhasil! Akun Anda sedang menunggu persetujuan dari admin.');
    }
}
