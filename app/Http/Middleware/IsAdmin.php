<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah user punya jabatan DAN jabatan tersebut adalah admin
        if (auth()->check() && auth()->user()->jabatan && auth()->user()->jabatan->is_admin) {
            return $next($request);
        }

        abort(403, 'ANDA TIDAK MEMILIKI AKSES KE HALAMAN INI.');
    }
}