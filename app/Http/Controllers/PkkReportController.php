<?php

namespace App\Http\Controllers;

use App\Models\KelompokPemuridan;
use App\Models\LaporanPertemuanKelompok;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PkkReportController extends Controller
{
    public function store(Request $request, KelompokPemuridan $kelompok): RedirectResponse
    {
        $pkk = $this->authorizeOwnedGroup($kelompok);
        $payload = $this->validateReportPayload($request, $kelompok);

        LaporanPertemuanKelompok::query()->create([
            ...$payload,
            'kelompok_id' => $kelompok->kelompok_id,
            'pkk_id' => $pkk->user_id,
        ]);

        return back()->with('success', 'Laporan pertemuan '.$kelompok->nama_kelompok.' berhasil disimpan.');
    }

    public function update(Request $request, LaporanPertemuanKelompok $laporan): RedirectResponse
    {
        $laporan->loadMissing('kelompok');
        $this->authorizeOwnedReport($laporan);

        $laporan->update($this->validateReportPayload($request, $laporan->kelompok));

        return back()->with('success', 'Laporan pertemuan berhasil diperbarui.');
    }

    public function destroy(LaporanPertemuanKelompok $laporan): RedirectResponse
    {
        $laporan->loadMissing('kelompok');
        $this->authorizeOwnedReport($laporan);

        $laporan->delete();

        return back()->with('success', 'Laporan pertemuan berhasil dihapus.');
    }

    private function validateReportPayload(Request $request, KelompokPemuridan $kelompok): array
    {
        $validated = $request->validate([
            'tanggal_pertemuan' => ['required', 'date'],
            'pertemuan_ke' => ['nullable', 'integer', 'between:1,999'],
            'bahan' => ['required', 'string', 'max:2000'],
            'ringkasan' => ['nullable', 'string', 'max:5000'],
            'anggota_hadir' => ['nullable', 'array'],
            'anggota_hadir.*' => ['integer', Rule::exists('users', 'user_id')],
            'catatan' => ['nullable', 'string', 'max:5000'],
            'rencana_lanjutan' => ['nullable', 'string', 'max:5000'],
            'kendala' => ['nullable', 'string', 'max:5000'],
        ], [], [
            'tanggal_pertemuan' => 'tanggal pertemuan',
            'pertemuan_ke' => 'pertemuan ke',
            'bahan' => 'bahan yang dibahas',
            'ringkasan' => 'ringkasan pembahasan',
            'anggota_hadir' => 'anggota yang hadir',
            'catatan' => 'catatan',
            'rencana_lanjutan' => 'rencana tindak lanjut',
            'kendala' => 'kendala',
        ]);

        $validMemberIds = $kelompok->anggota()
            ->pluck('user_id')
            ->map(fn ($id): int => (int) $id);
        $attendance = collect($validated['anggota_hadir'] ?? [])
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        if ($attendance->diff($validMemberIds)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'anggota_hadir' => 'Anggota hadir harus berasal dari kelompok yang sedang dilaporkan.',
            ]);
        }

        return [
            'tanggal_pertemuan' => $validated['tanggal_pertemuan'],
            'pertemuan_ke' => $validated['pertemuan_ke'] ?? null,
            'bahan' => $validated['bahan'],
            'ringkasan' => $validated['ringkasan'] ?? null,
            'anggota_hadir' => $attendance->all(),
            'jumlah_hadir' => $attendance->count(),
            'catatan' => $validated['catatan'] ?? null,
            'rencana_lanjutan' => $validated['rencana_lanjutan'] ?? null,
            'kendala' => $validated['kendala'] ?? null,
        ];
    }

    private function authorizeOwnedReport(LaporanPertemuanKelompok $laporan): User
    {
        $pkk = $this->authorizeOwnedGroup($laporan->kelompok);

        abort_unless(blank($laporan->pkk_id) || (int) $laporan->pkk_id === (int) $pkk->user_id, 403);

        return $pkk;
    }

    private function authorizeOwnedGroup(?KelompokPemuridan $kelompok): User
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless(
            $user && $user->isPKK() && $kelompok && (int) $kelompok->pemimpin_id === (int) $user->user_id,
            403
        );

        return $user;
    }
}
