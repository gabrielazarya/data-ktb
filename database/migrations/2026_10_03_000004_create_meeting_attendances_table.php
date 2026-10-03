<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Keep the old laporan_pertemuan_kelompok.anggota_hadir JSON column while
     * introducing one row per member and meeting. This lets old reports keep
     * rendering during the migration and gives new features a queryable source.
     */
    public function up(): void
    {
        if (! Schema::hasTable('meeting_attendances')) {
            Schema::create('meeting_attendances', function (Blueprint $table): void {
                $table->id('attendance_id');
                // laporan_id is the legacy name; meeting_id is the domain name
                // used by new code. Both point at the same report record.
                $table->unsignedBigInteger('meeting_id')->nullable();
                $table->unsignedBigInteger('laporan_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->string('status', 24)->default('hadir');
                $table->timestamp('recorded_at')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();

                $table->foreign('meeting_id')
                    ->references('laporan_id')
                    ->on('laporan_pertemuan_kelompok')
                    ->cascadeOnDelete();
                $table->foreign('laporan_id')
                    ->references('laporan_id')
                    ->on('laporan_pertemuan_kelompok')
                    ->cascadeOnDelete();
                $table->foreign('user_id')
                    ->references('user_id')
                    ->on('users')
                    ->cascadeOnDelete();

                $table->unique(['meeting_id', 'user_id']);
                $table->index(['laporan_id', 'status']);
                $table->index(['user_id', 'status']);
            });
        }

        $this->backfillLegacyAttendance();
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_attendances');
    }

    private function backfillLegacyAttendance(): void
    {
        if (! Schema::hasTable('laporan_pertemuan_kelompok')) {
            return;
        }

        DB::table('laporan_pertemuan_kelompok')
            ->select(['laporan_id', 'anggota_hadir', 'created_at'])
            ->orderBy('laporan_id')
            ->chunkById(500, function ($reports): void {
                foreach ($reports as $report) {
                    $raw = $report->anggota_hadir;
                    if (blank($raw)) {
                        continue;
                    }

                    $ids = is_array($raw) ? $raw : json_decode((string) $raw, true);
                    if (! is_array($ids)) {
                        continue;
                    }

                    foreach (collect($ids)->map(fn ($id): int => (int) $id)->filter()->unique() as $userId) {
                        DB::table('meeting_attendances')->insertOrIgnore([
                            'meeting_id' => $report->laporan_id,
                            'laporan_id' => $report->laporan_id,
                            'user_id' => $userId,
                            'status' => 'hadir',
                            'recorded_at' => $report->created_at,
                            'created_at' => $report->created_at,
                            'updated_at' => $report->created_at,
                        ]);
                    }
                }
            }, 'laporan_id');
    }
};
