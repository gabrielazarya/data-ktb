<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $actor = Auth::user();
        abort_unless($actor && $actor->isAdminEditor() && filled($actor->regio_id), 403);

        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:256'],
            'is_active' => ['nullable', 'boolean'],
        ], [], [
            'nama_lengkap' => 'nama staff',
            'is_active' => 'status aktif',
        ]);

        $username = 'staff_'.bin2hex(random_bytes(6));

        $staff = User::query()->create([
            'username' => $username,
            'password' => 'user',
            'nama_lengkap' => $validated['nama_lengkap'],
            'kampus_id' => null,
            'regio_id' => $actor->regio_id,
            'role' => 'staff',
            'admin_tipe' => null,
            'is_active' => $request->boolean('is_active'),
            'must_change_password' => true,
        ]);
        AuditLog::record('staff.created', $staff);

        return back()->with('success', 'Staff berhasil ditambahkan.');
    }
}
