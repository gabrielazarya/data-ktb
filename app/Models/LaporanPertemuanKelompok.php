<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class LaporanPertemuanKelompok extends Model
{
    use HasFactory;

    protected $table = 'laporan_pertemuan_kelompok';

    protected $primaryKey = 'laporan_id';

    protected $fillable = [
        'kelompok_id',
        'pkk_id',
        'tanggal_pertemuan',
        'pertemuan_ke',
        'bahan',
        'ringkasan',
        'anggota_hadir',
        'jumlah_hadir',
        'catatan',
        'rencana_lanjutan',
        'kendala',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pertemuan' => 'date',
            'anggota_hadir' => 'array',
        ];
    }

    protected static function booted(): void
    {
        // Reports can still be written directly by seeders/imports that know
        // only about the legacy JSON column. Mirror those values into the
        // normalized table whenever it is available.
        static::saved(function (LaporanPertemuanKelompok $report): void {
            if (! method_exists($report, 'syncAttendanceRows') || ! $report->exists) {
                return;
            }

            $report->syncAttendanceRows();
        });
    }

    public function kelompok()
    {
        return $this->belongsTo(KelompokPemuridan::class, 'kelompok_id', 'kelompok_id');
    }

    public function pelapor()
    {
        return $this->belongsTo(User::class, 'pkk_id', 'user_id');
    }

    public function attendanceRows()
    {
        return $this->hasMany(MeetingAttendance::class, 'meeting_id', 'laporan_id');
    }

    /**
     * Alias used by callers that use the Indonesian report terminology.
     */
    public function attendances()
    {
        return $this->attendanceRows();
    }

    /**
     * Return normalized attendance when present, falling back to the old JSON
     * representation so existing reports remain readable during rollout.
     */
    public function normalizedAttendanceIds(): array
    {
        if ($this->relationLoaded('attendanceRows')) {
            $rows = $this->attendanceRows->where('status', 'hadir');
        } else {
            $rows = $this->attendanceRows()->where('status', 'hadir')->get();
        }

        if ($rows->isNotEmpty()) {
            return $rows->pluck('user_id')->map(fn ($id): int => (int) $id)->values()->all();
        }

        return collect($this->anggota_hadir ?? [])
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Mirror legacy IDs into normalized attendance rows. When a member status
     * map is supplied, all members are stored (including izin/sakit/alpa).
     */
    public function syncAttendanceRows(?array $statuses = null, ?array $validMemberIds = null): void
    {
        if (! Schema::hasTable('meeting_attendances')) {
            return;
        }

        $presentIds = collect($this->anggota_hadir ?? [])
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->unique();

        if ($statuses === null) {
            $statuses = $presentIds->mapWithKeys(fn (int $id): array => [$id => 'hadir'])->all();
        }

        $statusMap = collect($statuses)
            ->mapWithKeys(function ($status, $id): array {
                $id = (int) $id;
                $status = in_array($status, ['hadir', 'izin', 'sakit', 'alpa'], true) ? $status : 'alpa';

                return [$id => $status];
            });

        $memberIds = collect($validMemberIds ?? $statusMap->keys())
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->unique();

        if ($memberIds->isEmpty()) {
            $memberIds = $statusMap->keys();
        }

        $this->attendanceRows()->delete();
        $now = now();
        $rows = $memberIds->map(function (int $userId) use ($statusMap, $now): array {
            return [
                'meeting_id' => $this->laporan_id,
                'laporan_id' => $this->laporan_id,
                'user_id' => $userId,
                'status' => $statusMap->get($userId, 'alpa'),
                'recorded_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->all();

        if ($rows !== []) {
            MeetingAttendance::query()->insert($rows);
        }
    }
}
