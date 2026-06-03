<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:256'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jurusan' => ['nullable', 'string', 'max:255'],
            'kategori_jurusan_id' => ['nullable', Rule::exists('kategori_jurusan', 'kategori_jurusan_id')],
            'angkatan' => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 1)],
            'foto_profil' => ['nullable', 'string', 'max:256'],
        ], [], [
            'nama_lengkap' => 'nama lengkap',
            'tanggal_lahir' => 'tanggal lahir',
            'jurusan' => 'jurusan',
            'kategori_jurusan_id' => 'kategori jurusan',
            'angkatan' => 'angkatan',
            'foto_profil' => 'foto profil',
        ]);

        $payload = [
            'nama_lengkap' => $validated['nama_lengkap'],
            'foto_profil' => blank($validated['foto_profil'] ?? null) ? null : $validated['foto_profil'],
        ];

        if (! $user->isSuperAdmin() && ! $user->isAdmin()) {
            $payload = array_merge($payload, [
                'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                'jurusan' => blank($validated['jurusan'] ?? null) ? null : $validated['jurusan'],
                'kategori_jurusan_id' => $validated['kategori_jurusan_id'] ?? null,
                'angkatan' => $validated['angkatan'] ?? null,
            ]);
        }

        $user->update($payload);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [], [
            'current_password' => 'password saat ini',
            'password' => 'password baru',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Password saat ini tidak sesuai.'])
                ->withInput();
        }

        $user->update([
            'password' => $validated['password'],
        ]);

        return back()->with('success', 'Password berhasil diubah.');
    }
}
