<?php

namespace App\Http\Controllers;

use App\Models\Regio;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RegioController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManageData();

        $validated = $this->validateRegio($request);

        Regio::query()->create([
            ...$this->payload($validated),
            'is_active' => true,
        ]);

        return back()->with('success', 'Data regio berhasil ditambahkan.');
    }

    public function update(Request $request, Regio $regio): RedirectResponse
    {
        $this->authorizeManageData();

        $validated = $this->validateRegio($request, $regio);

        $regio->update($this->payload($validated));

        return back()->with('success', 'Data regio berhasil diperbarui.');
    }

    private function validateRegio(Request $request, ?Regio $regio = null): array
    {
        return $request->validate([
            'nama_regio' => [
                'required',
                'string',
                'max:100',
                Rule::unique('regios', 'nama_regio')->ignore($regio?->regio_id, 'regio_id'),
            ],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ], [], [
            'nama_regio' => 'nama regio',
            'keterangan' => 'keterangan',
        ]);
    }

    private function payload(array $validated): array
    {
        return [
            'nama_regio' => $validated['nama_regio'],
            'keterangan' => blank($validated['keterangan'] ?? null) ? null : $validated['keterangan'],
        ];
    }

    private function authorizeManageData(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless(
            $user && $user->isSuperAdmin(),
            403
        );
    }
}
