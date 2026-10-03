<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupLeaderHistory extends Model
{
    use HasFactory;

    protected $table = 'group_leader_histories';

    protected $primaryKey = 'history_id';

    protected $fillable = [
        'kelompok_id',
        'leader_id',
        'started_at',
        'ended_at',
        'is_current',
        'reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_current' => 'boolean',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function kelompok()
    {
        return $this->belongsTo(KelompokPemuridan::class, 'kelompok_id', 'kelompok_id');
    }

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id', 'user_id');
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true)->whereNull('ended_at');
    }
}
