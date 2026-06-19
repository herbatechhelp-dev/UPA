<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Bypass check if user is a Superadmin (full system privileges)
        if ($user->isSuperadmin()) {
            return $next($request);
        }

        // Verifikasi apakah user memiliki role yang sesuai (berdasarkan role slug)
        if (!$user->hasRole($role)) {
            abort(403, 'Akses ditolak: Otoritas Anda tidak mencukupi untuk membuka halaman ini.');
        }

        return $next($request);
    }
}
