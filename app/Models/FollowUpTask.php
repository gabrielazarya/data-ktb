<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowUpTask extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_DONE = 'done';

    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'follow_up_tasks';

    protected $primaryKey = 'follow_up_id';

    protected $fillable = [
        'kelompok_id',
        'laporan_id',
        'user_id',
        'assigned_to',
        'created_by',
        'title',
        'reason',
        'description',
        'due_at',
        'status',
        'completed_at',
        'completed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function kelompok()
    {
        return $this->belongsTo(KelompokPemuridan::class, 'kelompok_id', 'kelompok_id');
    }

    public function laporan()
    {
        return $this->belongsTo(LaporanPertemuanKelompok::class, 'laporan_id', 'laporan_id');
    }

    public function member()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function completer()
    {
        return $this->belongsTo(User::class, 'completed_by', 'user_id');
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', [self::STATUS_OPEN, self::STATUS_IN_PROGRESS]);
    }

    public function markDone(?User $user = null, ?string $notes = null): bool
    {
        return $this->update([
            'status' => self::STATUS_DONE,
            'completed_at' => now(),
            'completed_by' => $user?->user_id,
            'notes' => $notes ?? $this->notes,
        ]);
    }
}
