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
        $actor = $this->authorizeManageData();

        $validated = $this->validateKampus($request, actor: $actor);

        Kampus::query()->create($this->payload($request, $validated, $actor));

        return back()->with('success', 'Data kampus berhasil ditambahkan.');
    }

    public function update(Request $request, Kampus $kampus): RedirectResponse
    {
        $actor = $this->authorizeManageData();
        $this->authorizeKampusAccess($kampus, $actor);

        $validated = $this->validateKampus($request, $kampus, $actor);

        $kampus->update($this->payload($request, $validated, $actor));

        return back()->with('success', 'Data kampus berhasil diperbarui.');
    }

    public function destroy(Kampus $kampus): RedirectResponse
    {
        $actor = $this->authorizeManageData();
        $this->authorizeKampusAccess($kampus, $actor);

        $kampus->delete();

        return back()->with('success', 'Data kampus berhasil dihapus.');
    }

    private function validateKampus(Request $request, ?Kampus $kampus = null, ?User $actor = null): array
    {
        $rules = [
            'nama_kampus' => [
                'required',
                'string',
                'max:256',
                Rule::unique('kampus', 'nama_kampus')->ignore($kampus?->kampus_id, 'kampus_id'),
            ],
            'singkatan' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ];

        if ($actor?->isSuperAdmin()) {
            $rules['regio_id'] = ['nullable', 'integer', Rule::exists('regios', 'regio_id')];
        }

        return $request->validate($rules, [], [
            'nama_kampus' => 'nama kampus',
            'regio_id' => 'regio',
            'singkatan' => 'singkatan',
            'is_active' => 'status aktif',
        ]);
    }

    private function payload(Request $request, array $validated, User $actor): array
    {
        return [
            'nama_kampus' => $validated['nama_kampus'],
            'regio_id' => $this->resolveRegioId($actor, $validated['regio_id'] ?? null),
            'singkatan' => blank($validated['singkatan'] ?? null) ? null : $validated['singkatan'],
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function resolveRegioId(User $actor, mixed $regioId = null): int
    {
        if (! $actor->isSuperAdmin()) {
            abort_unless(filled($actor->regio_id), 403);

            return (int) $actor->regio_id;
        }

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

    private function authorizeKampusAccess(Kampus $kampus, User $actor): void
    {
        if ($actor->isSuperAdmin()) {
            return;
        }

        abort_unless(filled($actor->regio_id) && (int) $kampus->regio_id === (int) $actor->regio_id, 403);
    }

    private function authorizeManageData(): User
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless(
            $user && $user->isAdminEditor(),
            403
        );

        return $user;
    }
}
