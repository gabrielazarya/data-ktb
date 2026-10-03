<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mentorship extends Model
{
    use HasFactory;

    protected $table = 'mentorships';

    protected $primaryKey = 'mentorship_id';

    protected $fillable = [
        'mentor_id',
        'mentee_id',
        'is_primary',
        'status',
        'started_at',
        'ended_at',
        'reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id', 'user_id');
    }

    public function mentee()
    {
        return $this->belongsTo(User::class, 'mentee_id', 'user_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')->whereNull('ended_at');
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}
