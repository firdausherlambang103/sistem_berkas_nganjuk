<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();

        // [BARU] Logika Redirect Khusus
        if ($user->jabatan) {
            if ($user->jabatan->nama_jabatan === 'PPAT') {
                return redirect()->route('ppat.dashboard'); // Pastikan route ini ada
            } elseif ($user->jabatan->nama_jabatan === 'Freelance') {
                return redirect()->route('freelance.dashboard'); // Pastikan route ini ada
            }
        }

        // Default redirect
        return redirect()->intended(RouteServiceProvider::HOME); // Biasanya /dashboard
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
