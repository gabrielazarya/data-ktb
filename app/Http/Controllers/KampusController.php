<?php

namespace App\Http\Controllers;

use App\Models\Kampus;
use App\Models\Regio;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class KampusController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManageData();

        $validated = $this->validateKampus($request);

        Kampus::query()->create($this->payload($request, $validated));

        return back()->with('success', 'Data kampus berhasil ditambahkan.');
    }

    public function update(Request $request, Kampus $kampus): RedirectResponse
    {
        $this->authorizeManageData();

        $validated = $this->validateKampus($request, $kampus);

        $kampus->update($this->payload($request, $validated));

        return back()->with('success', 'Data kampus berhasil diperbarui.');
    }

    public function destroy(Kampus $kampus): RedirectResponse
    {
        $this->authorizeManageData();

        $kampus->delete();

        return back()->with('success', 'Data kampus berhasil dihapus.');
    }

    private function validateKampus(Request $request, ?Kampus $kampus = null): array
    {
        return $request->validate([
            'nama_kampus' => [
                'required',
                'string',
                'max:256',
                Rule::unique('kampus', 'nama_kampus')->ignore($kampus?->kampus_id, 'kampus_id'),
            ],
            'regio_id' => ['nullable', 'integer', Rule::exists('regios', 'regio_id')],
            'singkatan' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ], [], [
            'nama_kampus' => 'nama kampus',
            'regio_id' => 'regio',
            'singkatan' => 'singkatan',
            'is_active' => 'status aktif',
        ]);
    }

    private function payload(Request $request, array $validated): array
    {
        return [
            'nama_kampus' => $validated['nama_kampus'],
            'regio_id' => $this->resolveRegioId($validated['regio_id'] ?? null),
            'singkatan' => blank($validated['singkatan'] ?? null) ? null : $validated['singkatan'],
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function resolveRegioId(mixed $regioId = null): int
    {
        if (filled($regioId)) {
            return (int) $regioId;
        }

        return (int) Regio::query()->firstOrCreate(
            ['nama_regio' => 'Surabaya'],
            [
                'keterangan' => 'Wilayah pelayanan PMK Kota Surabaya',
                'is_active' => true,
            ]
        )->regio_id;
    }

    private function authorizeManageData(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless(
            $user && ($user->isSuperAdmin() || $user->isAdminEditor()),
            403
        );
    }
}
