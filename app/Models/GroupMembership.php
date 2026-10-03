<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMembership extends Model
{
    use HasFactory;

    protected $table = 'group_memberships';

    protected $primaryKey = 'membership_id';

    protected $fillable = [
        'user_id',
        'kelompok_id',
        'role',
        'status',
        'started_at',
        'ended_at',
        'reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function kelompok()
    {
        return $this->belongsTo(KelompokPemuridan::class, 'kelompok_id', 'kelompok_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')->whereNull('ended_at');
    }

    public function scopeCurrent($query)
    {
        return $query->active();
    }
}
