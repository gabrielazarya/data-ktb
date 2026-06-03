<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccessSwitchController extends Controller
{
    private const SESSION_KEY = 'impersonator_super_admin_id';

    public function switchToAdmin(Request $request, User $user): RedirectResponse
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();

        abort_unless($currentUser && $currentUser->isSuperAdmin(), 403);
        abort_unless(in_array($user->role, ['admin', 'pkk', 'akk'], true), 404);

        $superAdminId = $currentUser->getKey();

        Auth::login($user);
        $request->session()->put(self::SESSION_KEY, $superAdminId);

        return redirect()
            ->route($this->dashboardRouteFor($user))
            ->with('success', 'Sekarang memakai akses '.$this->accessLabelFor($user).' '.$user->nama_lengkap.'.');
    }

    public function returnToSuperAdmin(Request $request): RedirectResponse
    {
        $superAdminId = $request->session()->get(self::SESSION_KEY);

        abort_unless($superAdminId, 403);

        $superAdmin = User::query()
            ->whereKey($superAdminId)
            ->where('role', 'super_admin')
            ->firstOrFail();

        Auth::login($superAdmin);
        $request->session()->forget(self::SESSION_KEY);

        return redirect()
            ->route('superadmin.dashboard')
            ->with('success', 'Akses superadmin sudah dikembalikan.');
    }

    private function dashboardRouteFor(User $user): string
    {
        return match ($user->role) {
            'admin' => 'admin.dashboard',
            'pkk' => 'pkk.dashboard',
            'akk' => 'akk.dashboard',
            default => 'dashboard',
        };
    }

    private function accessLabelFor(User $user): string
    {
        return match ($user->role) {
            'admin' => 'admin',
            'pkk' => 'PKK',
            'akk' => 'AKK',
            default => 'pengguna',
        };
    }
}
