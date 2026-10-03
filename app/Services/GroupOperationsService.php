<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\KelompokPemuridan;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mutations that affect the operational structure of a discipleship group.
 *
 * The application still has the original one-group-per-user columns.  The
 * service writes those columns for backwards compatibility and, when the
 * normalized tables are available, writes the corresponding history row too.
 * This lets the migration to group_memberships/group_campuses be deployed
 * without taking the existing dashboard offline.
 */
class GroupOperationsService
{
    public function transferMember(User $member, KelompokPemuridan $target, ?string $reason = null): void
    {
        $now = Carbon::now();
        $leaderId = $target->pemimpin_id;

        DB::transaction(function () use ($member, $target, $reason, $now, $leaderId): void {
            $this->closeMemberships($member->user_id, $now, $reason);

            $member->forceFill([
                'kelompok_id' => $target->kelompok_id,
                'pkk_id' => $leaderId,
                'regio_id' => $target->regio_id ?: $target->kampus?->regio_id ?: $member->regio_id,
            ])->save();

            $this->insertMembership([
                'user_id' => $member->user_id,
                'kelompok_id' => $target->kelompok_id,
                'role' => 'member',
                'status' => 'active',
                'started_at' => $now,
                'reason' => $reason,
            ]);
            if ($target->pemimpin) {
                $this->recordMentorship($target->pemimpin, $member, $reason);
            }
            AuditLog::record('member.transferred', $member, [
                'target_group_id' => $target->kelompok_id,
                'reason' => $reason,
            ]);
        });
    }

    public function recordMemberAssignment(User $member, ?KelompokPemuridan $group, ?string $reason = null): void
    {
        if (! $group || ! $this->hasTable('group_memberships')) {
            return;
        }

        $this->closeMemberships($member->user_id, Carbon::now(), $reason);
        $this->insertMembership([
            'user_id' => $member->user_id,
            'kelompok_id' => $group->kelompok_id,
            'role' => in_array($member->role, ['pkk', 'staff'], true) ? 'mentor' : 'member',
            'status' => 'active',
            'started_at' => Carbon::now(),
            'reason' => $reason,
        ]);
    }

    public function recordLeaderAssignment(User $leader, KelompokPemuridan $group, ?string $reason = null): void
    {
        if (! $this->hasTable('group_leader_histories')) {
            return;
        }

        $now = Carbon::now();
        DB::table('group_leader_histories')
            ->where('kelompok_id', $group->kelompok_id)
            ->where('is_current', true)
            ->update([
                'is_current' => false,
                'ended_at' => $now,
                'updated_at' => $now,
            ]);

        DB::table('group_leader_histories')->insert([
            'kelompok_id' => $group->kelompok_id,
            'leader_id' => $leader->user_id,
            'started_at' => $now,
            'is_current' => true,
            'reason' => $reason,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function recordMentorship(User $mentor, User $mentee, ?string $reason = null): void
    {
        if (! $this->hasTable('mentorships') || (int) $mentor->user_id === (int) $mentee->user_id) {
            return;
        }

        $now = Carbon::now();
        DB::transaction(function () use ($mentor, $mentee, $reason, $now): void {
            $columns = Schema::getColumnListing('mentorships');
            DB::table('mentorships')
                ->where('mentee_id', $mentee->user_id)
                ->where('is_primary', true)
                ->whereNull('ended_at')
                ->update($this->filterColumns([
                    'status' => 'ended',
                    'ended_at' => $now,
                    'reason' => $reason,
                    'updated_at' => $now,
                ], $columns));

            $this->insertMentorship([
                'mentor_id' => $mentor->user_id,
                'mentee_id' => $mentee->user_id,
                'is_primary' => true,
                'status' => 'active',
                'started_at' => $now,
                'reason' => $reason,
            ]);
        });
    }

    public function reassignLeader(KelompokPemuridan $group, User $leader): void
    {
        $now = Carbon::now();

        DB::transaction(function () use ($group, $leader, $now): void {
            $oldLeaderId = $group->pemimpin_id;

            $group->forceFill([
                'pemimpin_id' => $leader->user_id,
                'regio_id' => $group->regio_id ?: $group->kampus?->regio_id ?: $leader->regio_id,
            ])->save();
            $this->recordLeaderAssignment($leader, $group, 'Pergantian pemimpin kelompok');

            if ($oldLeaderId && $oldLeaderId !== $leader->user_id && $this->hasTable('group_memberships')) {
                $this->updateMembershipRole($oldLeaderId, $group->kelompok_id, 'member');
            }

            $leader->forceFill([
                'role' => $leader->isStaff() ? 'staff' : 'pkk',
                'regio_id' => $group->regio_id ?: $group->kampus?->regio_id ?: $leader->regio_id,
                'admin_tipe' => null,
            ])->save();

            $members = User::query()
                ->where('kelompok_id', $group->kelompok_id)
                ->where('is_active', true)
                ->get();
            $members->each(function (User $member) use ($leader): void {
                if ((int) $member->user_id === (int) $leader->user_id) {
                    return;
                }
                $member->forceFill(['pkk_id' => $leader->user_id])->save();
                $this->recordMentorship($leader, $member, 'Pergantian pemimpin kelompok');
            });

            if ($this->hasTable('group_memberships')) {
                $existingMembership = DB::table('group_memberships')
                    ->where('user_id', $leader->user_id)
                    ->where('kelompok_id', $group->kelompok_id)
                    ->whereNull('ended_at')
                    ->first();
                if ($existingMembership) {
                    $this->updateMembershipRole($leader->user_id, $group->kelompok_id, 'leader');
                } else {
                    $this->insertMembership([
                        'user_id' => $leader->user_id,
                        'kelompok_id' => $group->kelompok_id,
                        'role' => 'leader',
                        'status' => 'active',
                        'started_at' => $now,
                    ]);
                }
            }
            AuditLog::record('group.leader_reassigned', $group, [
                'old_leader_id' => $oldLeaderId,
                'new_leader_id' => $leader->user_id,
            ]);
        });
    }

    public function assignCampus(KelompokPemuridan $group, int $campusId, bool $primary = false, ?string $assignmentType = null): void
    {
        $now = Carbon::now();

        DB::transaction(function () use ($group, $campusId, $primary, $assignmentType, $now): void {
            if (! $this->hasTable('group_campuses')) {
                // Legacy installations have only a single campus column.  A
                // secondary assignment is represented by the normalized table
                // once its migration has been installed.
                $group->forceFill(['kampus_id' => $campusId])->save();

                return;
            }

            $columns = Schema::getColumnListing('group_campuses');
            $existing = DB::table('group_campuses')
                ->where('kelompok_id', $group->kelompok_id)
                ->where('kampus_id', $campusId)
                ->first();

            $payload = $this->filterColumns([
                'kelompok_id' => $group->kelompok_id,
                'kampus_id' => $campusId,
                'is_primary' => $primary,
                'assignment_type' => $assignmentType ?: ($primary ? 'primary' : 'service'),
                'created_at' => $now,
                'updated_at' => $now,
            ], $columns);

            if ($existing) {
                DB::table('group_campuses')
                    ->where('kelompok_id', $group->kelompok_id)
                    ->where('kampus_id', $campusId)
                    ->update($this->filterColumns($payload, $columns, ['created_at']));
            } else {
                DB::table('group_campuses')->insert($payload);
            }

            if ($primary) {
                DB::table('group_campuses')
                    ->where('kelompok_id', $group->kelompok_id)
                    ->where('kampus_id', '!=', $campusId)
                    ->update($this->filterColumns([
                        'is_primary' => false,
                        'updated_at' => $now,
                    ], $columns));

                // Keep the legacy projection in sync for old pages.
                $group->forceFill(['kampus_id' => $campusId])->save();
            }
        });
    }

    public function archiveGroup(KelompokPemuridan $group, ?string $reason = null): void
    {
        $now = Carbon::now();

        DB::transaction(function () use ($group, $reason, $now): void {
            $columns = Schema::hasTable('kelompok_pemuridan')
                ? Schema::getColumnListing('kelompok_pemuridan')
                : [];
            $payload = ['is_active' => false, 'updated_at' => $now];
            if (in_array('archived_at', $columns, true)) {
                $payload['archived_at'] = $now;
            }
            if (in_array('archive_reason', $columns, true) && filled($reason)) {
                $payload['archive_reason'] = $reason;
            }
            $group->forceFill($payload)->save();

            if ($this->hasTable('group_memberships')) {
                DB::table('group_memberships')
                    ->where('kelompok_id', $group->kelompok_id)
                    ->whereNull('ended_at')
                    ->update($this->filterColumns([
                        'ended_at' => $now,
                        'status' => 'ended',
                        'updated_at' => $now,
                    ], Schema::getColumnListing('group_memberships')));
            }
            if ($this->hasTable('group_leader_histories')) {
                DB::table('group_leader_histories')
                    ->where('kelompok_id', $group->kelompok_id)
                    ->where('is_current', true)
                    ->update([
                        'is_current' => false,
                        'ended_at' => $now,
                        'updated_at' => $now,
                    ]);
            }
            User::query()
                ->where('kelompok_id', $group->kelompok_id)
                ->pluck('user_id')
                ->each(fn ($userId) => $this->closeMentorships((int) $userId, $now, $reason));
            AuditLog::record('group.archived', $group, ['reason' => $reason]);
        });
    }

    public function archiveMember(User $member, ?string $reason = null): void
    {
        $now = Carbon::now();

        DB::transaction(function () use ($member, $reason, $now): void {
            $member->forceFill([
                'is_active' => false,
                'lifecycle_status' => 'nonaktif',
                'lifecycle_changed_at' => $now,
                'lifecycle_reason' => $reason,
            ])->save();
            $this->closeMemberships($member->user_id, $now, $reason);
            $this->closeMentorships($member->user_id, $now, $reason);
            AuditLog::record('member.archived', $member, ['reason' => $reason]);
        });
    }

    public function changeMemberLifecycle(User $member, string $status, ?string $reason = null): void
    {
        $now = Carbon::now();
        $isActive = $status === 'active';

        DB::transaction(function () use ($member, $status, $reason, $now, $isActive): void {
            $member->forceFill([
                'is_active' => $isActive,
                'lifecycle_status' => $status,
                'lifecycle_changed_at' => $now,
                'lifecycle_reason' => $reason,
            ])->save();

            if (! $isActive) {
                $this->closeMemberships($member->user_id, $now, $reason);
                $this->closeMentorships($member->user_id, $now, $reason);
            } elseif ($member->kelompok_id) {
                $group = KelompokPemuridan::query()
                    ->with('pemimpin')
                    ->whereKey($member->kelompok_id)
                    ->where('is_active', true)
                    ->first();
                if ($group) {
                    $this->recordMemberAssignment($member, $group, 'Anggota diaktifkan kembali');
                    if ($group->pemimpin) {
                        $this->recordMentorship($group->pemimpin, $member, 'Anggota diaktifkan kembali');
                    }
                }
            }

            AuditLog::record('member.lifecycle_changed', $member, [
                'status' => $status,
                'reason' => $reason,
            ]);
        });
    }

    private function closeMemberships(int $userId, Carbon $now, ?string $reason = null): void
    {
        if (! $this->hasTable('group_memberships')) {
            return;
        }

        $columns = Schema::getColumnListing('group_memberships');
        DB::table('group_memberships')
            ->where('user_id', $userId)
            ->whereNull('ended_at')
            ->update($this->filterColumns([
                'ended_at' => $now,
                'status' => 'ended',
                'reason' => $reason,
                'updated_at' => $now,
            ], $columns));
    }

    private function insertMembership(array $payload): void
    {
        if (! $this->hasTable('group_memberships')) {
            return;
        }

        $columns = Schema::getColumnListing('group_memberships');
        $payload['created_at'] ??= Carbon::now();
        $payload['updated_at'] ??= Carbon::now();
        DB::table('group_memberships')->insert($this->filterColumns($payload, $columns));
    }

    private function insertMentorship(array $payload): void
    {
        if (! $this->hasTable('mentorships')) {
            return;
        }

        $columns = Schema::getColumnListing('mentorships');
        $payload['created_at'] ??= Carbon::now();
        $payload['updated_at'] ??= Carbon::now();
        DB::table('mentorships')->insert($this->filterColumns($payload, $columns));
    }

    private function closeMentorships(int $menteeId, Carbon $now, ?string $reason = null): void
    {
        if (! $this->hasTable('mentorships')) {
            return;
        }

        $columns = Schema::getColumnListing('mentorships');
        DB::table('mentorships')
            ->where('mentee_id', $menteeId)
            ->whereNull('ended_at')
            ->update($this->filterColumns([
                'status' => 'ended',
                'ended_at' => $now,
                'reason' => $reason,
                'updated_at' => $now,
            ], $columns));
    }

    private function updateMembershipRole(int $userId, int $groupId, string $role): void
    {
        $columns = Schema::getColumnListing('group_memberships');
        $payload = $this->filterColumns(['role' => $role, 'updated_at' => Carbon::now()], $columns);

        DB::table('group_memberships')
            ->where('user_id', $userId)
            ->where('kelompok_id', $groupId)
            ->whereNull('ended_at')
            ->update($payload);
    }

    private function filterColumns(array $payload, array $columns, array $except = []): array
    {
        return array_intersect_key($payload, array_flip(array_diff($columns, $except)));
    }

    private function hasTable(string $table): bool
    {
        return Schema::hasTable($table);
    }
}
