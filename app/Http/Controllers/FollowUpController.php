<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\FollowUpTask;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class FollowUpController extends Controller
{
    public function update(Request $request, FollowUpTask $followUp): RedirectResponse
    {
        /** @var User|null $actor */
        $actor = Auth::user();
        abort_unless($actor && ($actor->isSuperAdmin() || $actor->isAdminEditor() || $actor->canLeadGroups()), 403);
        $this->authorizeTask($followUp, $actor);

        $validated = $request->validate([
            'status' => ['required', Rule::in([
                FollowUpTask::STATUS_OPEN,
                FollowUpTask::STATUS_IN_PROGRESS,
                FollowUpTask::STATUS_DONE,
                FollowUpTask::STATUS_CANCELLED,
            ])],
            'notes' => ['nullable', 'string', 'max:5000'],
        ], [], [
            'status' => 'status tindak lanjut',
            'notes' => 'catatan tindak lanjut',
        ]);

        $payload = [
            'status' => $validated['status'],
            'notes' => blank($validated['notes'] ?? null) ? null : $validated['notes'],
        ];

        if ($validated['status'] === FollowUpTask::STATUS_DONE) {
            $payload['completed_at'] = now();
            $payload['completed_by'] = $actor->user_id;
        } else {
            $payload['completed_at'] = null;
            $payload['completed_by'] = null;
        }

        $followUp->update($payload);
        AuditLog::record('follow_up.updated', $followUp, ['status' => $validated['status']]);

        return back()->with('success', 'Tindak lanjut berhasil diperbarui.');
    }

    private function authorizeTask(FollowUpTask $task, User $actor): void
    {
        if ($actor->isAdminEditor() || $actor->isSuperAdmin()) {
            if ($actor->isSuperAdmin()) {
                return;
            }

            abort_unless(
                filled($actor->regio_id)
                && (int) ($task->kelompok?->regio_id ?: $task->kelompok?->kampus?->regio_id) === (int) $actor->regio_id,
                403
            );

            return;
        }

        abort_unless((int) $task->assigned_to === (int) $actor->user_id || (int) $task->created_by === (int) $actor->user_id, 403);
    }
}
