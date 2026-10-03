<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Introduce historical membership and cross-campus relationships while
     * keeping the legacy users.kelompok_id/pkk_id columns available for a
     * gradual dual-read/dual-write rollout.
     */
    public function up(): void
    {
        $this->addLifecycleColumns();
        $this->createMembershipsTable();
        $this->createGroupCampusesTable();
        $this->createMentorshipsTable();
        $this->createLeaderHistoryTable();

        $this->backfillGroupCampuses();
        $this->backfillMemberships();
        $this->backfillMentorships();
        $this->backfillLeaderHistory();
    }

    public function down(): void
    {
        Schema::dropIfExists('group_leader_histories');
        Schema::dropIfExists('mentorships');
        Schema::dropIfExists('group_campuses');
        Schema::dropIfExists('group_memberships');

        if (Schema::hasColumn('users', 'lifecycle_reason')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropIndex(['lifecycle_status']);
                $table->dropColumn(['lifecycle_status', 'lifecycle_changed_at', 'lifecycle_reason']);
            });
        }
    }

    private function addLifecycleColumns(): void
    {
        if (Schema::hasColumn('users', 'lifecycle_status')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->string('lifecycle_status', 32)->default('active')->after('is_active');
            $table->timestamp('lifecycle_changed_at')->nullable()->after('lifecycle_status');
            $table->string('lifecycle_reason', 255)->nullable()->after('lifecycle_changed_at');
            $table->index('lifecycle_status');
        });

        DB::table('users')->whereNull('lifecycle_changed_at')->update([
            'lifecycle_changed_at' => now(),
        ]);
    }

    private function createMembershipsTable(): void
    {
        if (Schema::hasTable('group_memberships')) {
            return;
        }

        Schema::create('group_memberships', function (Blueprint $table): void {
            $table->id('membership_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('kelompok_id');
            $table->string('role', 32)->default('member');
            $table->string('status', 32)->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('reason', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->foreign('kelompok_id')->references('kelompok_id')->on('kelompok_pemuridan')->cascadeOnDelete();
            $table->index(['user_id', 'status']);
            $table->index(['kelompok_id', 'status']);
            $table->index(['user_id', 'kelompok_id', 'started_at']);
        });
    }

    private function createGroupCampusesTable(): void
    {
        if (Schema::hasTable('group_campuses')) {
            return;
        }

        Schema::create('group_campuses', function (Blueprint $table): void {
            $table->id('group_campus_id');
            $table->unsignedBigInteger('kelompok_id');
            $table->unsignedBigInteger('kampus_id');
            $table->boolean('is_primary')->default(false);
            $table->string('assignment_type', 32)->default('service');
            $table->timestamps();

            $table->foreign('kelompok_id')->references('kelompok_id')->on('kelompok_pemuridan')->cascadeOnDelete();
            $table->foreign('kampus_id')->references('kampus_id')->on('kampus')->cascadeOnDelete();
            $table->unique(['kelompok_id', 'kampus_id']);
            $table->index(['kampus_id', 'is_primary']);
        });
    }

    private function createMentorshipsTable(): void
    {
        if (Schema::hasTable('mentorships')) {
            return;
        }

        Schema::create('mentorships', function (Blueprint $table): void {
            $table->id('mentorship_id');
            $table->unsignedBigInteger('mentor_id');
            $table->unsignedBigInteger('mentee_id');
            $table->boolean('is_primary')->default(true);
            $table->string('status', 32)->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('reason', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('mentor_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->foreign('mentee_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->index(['mentor_id', 'status']);
            $table->index(['mentee_id', 'status']);
            $table->index(['mentor_id', 'mentee_id', 'started_at']);
        });
    }

    private function createLeaderHistoryTable(): void
    {
        if (Schema::hasTable('group_leader_histories')) {
            return;
        }

        Schema::create('group_leader_histories', function (Blueprint $table): void {
            $table->id('history_id');
            $table->unsignedBigInteger('kelompok_id');
            $table->unsignedBigInteger('leader_id');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->boolean('is_current')->default(true);
            $table->string('reason', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('kelompok_id')->references('kelompok_id')->on('kelompok_pemuridan')->cascadeOnDelete();
            $table->foreign('leader_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->index(['kelompok_id', 'is_current']);
            $table->index(['leader_id', 'is_current']);
        });
    }

    private function backfillGroupCampuses(): void
    {
        if (! Schema::hasTable('group_campuses')) {
            return;
        }

        $now = now();
        DB::table('kelompok_pemuridan')
            ->whereNotNull('kampus_id')
            ->select(['kelompok_id', 'kampus_id'])
            ->orderBy('kelompok_id')
            ->chunkById(500, function ($groups) use ($now): void {
                $rows = $groups->map(static fn ($group): array => [
                    'kelompok_id' => $group->kelompok_id,
                    'kampus_id' => $group->kampus_id,
                    'is_primary' => true,
                    'assignment_type' => 'primary',
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                if ($rows !== []) {
                    DB::table('group_campuses')->insertOrIgnore($rows);
                }
            }, 'kelompok_id');
    }

    private function backfillMemberships(): void
    {
        $now = now();
        DB::table('users')
            ->whereNotNull('kelompok_id')
            ->select(['user_id', 'kelompok_id', 'role', 'created_at'])
            ->orderBy('user_id')
            ->chunkById(500, function ($users) use ($now): void {
                $groupIds = $users->pluck('kelompok_id')->filter()->unique()->values();
                $leaders = DB::table('kelompok_pemuridan')
                    ->whereIn('kelompok_id', $groupIds)
                    ->pluck('pemimpin_id', 'kelompok_id');

                $rows = $users->map(static function ($user) use ($leaders, $now): array {
                    $isLeader = (int) ($leaders[$user->kelompok_id] ?? 0) === (int) $user->user_id;
                    $role = $isLeader ? 'leader' : (in_array($user->role, ['pkk', 'staff'], true) ? 'mentor' : 'member');

                    return [
                        'user_id' => $user->user_id,
                        'kelompok_id' => $user->kelompok_id,
                        'role' => $role,
                        'status' => 'active',
                        'started_at' => $user->created_at ?: $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                })->all();

                if ($rows !== []) {
                    DB::table('group_memberships')->insertOrIgnore($rows);
                }
            }, 'user_id');
    }

    private function backfillMentorships(): void
    {
        $now = now();
        DB::table('users')
            ->whereNotNull('pkk_id')
            ->whereColumn('pkk_id', '!=', 'user_id')
            ->select(['user_id', 'pkk_id', 'created_at'])
            ->orderBy('user_id')
            ->chunkById(500, function ($users) use ($now): void {
                $rows = $users->map(static fn ($user): array => [
                    'mentor_id' => $user->pkk_id,
                    'mentee_id' => $user->user_id,
                    'is_primary' => true,
                    'status' => 'active',
                    'started_at' => $user->created_at ?: $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                if ($rows !== []) {
                    DB::table('mentorships')->insertOrIgnore($rows);
                }
            }, 'user_id');
    }

    private function backfillLeaderHistory(): void
    {
        $now = now();
        DB::table('kelompok_pemuridan')
            ->select(['kelompok_id', 'pemimpin_id', 'created_at'])
            ->orderBy('kelompok_id')
            ->chunkById(500, function ($groups) use ($now): void {
                $rows = $groups->map(static fn ($group): array => [
                    'kelompok_id' => $group->kelompok_id,
                    'leader_id' => $group->pemimpin_id,
                    'started_at' => $group->created_at ?: $now,
                    'is_current' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                if ($rows !== []) {
                    DB::table('group_leader_histories')->insertOrIgnore($rows);
                }
            }, 'kelompok_id');
    }
};
