<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user()->role);
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $username = trim((string) $request->username);
        $throttleKey = 'login:'.strtolower($username).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'username' => 'Terlalu banyak percobaan login. Coba lagi dalam '.$seconds.' detik.',
            ]);
        }

        $credentials = [
            'username' => $username,
            'password' => $request->password,
            'is_active' => true,
            'lifecycle_status' => 'active',
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            $user = Auth::user();
            $user->forceFill(['last_login_at' => now()])->saveQuietly();
            AuditLog::record('auth.login', $user);

            if ($user->must_change_password) {
                return redirect()->route('dashboard.profile')
                    ->with('warning', 'Silakan ganti password sementara sebelum melanjutkan.');
            }

            return $this->redirectByRole(Auth::user()->role);
        }

        RateLimiter::hit($throttleKey, 60);
        AuditLog::record('auth.login_failed', null, ['username' => $username]);

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->withInput($request->only('username'));
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            AuditLog::record('auth.logout', Auth::user());
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }

    private function redirectByRole(string $role)
    {
        return match ($role) {
            'super_admin' => redirect()->route('superadmin.dashboard'),
            'admin' => redirect()->route('admin.dashboard'),
            'pkk' => redirect()->route('pkk.dashboard'),
            'staff' => redirect()->route('pkk.dashboard'),
            'akk' => redirect()->route('akk.dashboard'),
            default => redirect()->route('landing'),
        };
    }
}
