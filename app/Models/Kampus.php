<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kampus extends Model
{
    use HasFactory;

    protected $table = 'kampus';

    protected $primaryKey = 'kampus_id';

    protected $fillable = [
        'regio_id',
        'nama_kampus',
        'singkatan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Semua user yang berasal dari kampus ini
     */
    public function users()
    {
        return $this->hasMany(User::class, 'kampus_id', 'kampus_id');
    }

    public function regio()
    {
        return $this->belongsTo(Regio::class, 'regio_id', 'regio_id');
    }

    public function kelompokPemuridan()
    {
        return $this->hasMany(KelompokPemuridan::class, 'kampus_id', 'kampus_id');
    }

    public function groupAssignments()
    {
        return $this->hasMany(GroupCampus::class, 'kampus_id', 'kampus_id');
    }

    public function groups()
    {
        return $this->belongsToMany(
            KelompokPemuridan::class,
            'group_campuses',
            'kampus_id',
            'kelompok_id',
            'kampus_id',
            'kelompok_id'
        )->withPivot(['group_campus_id', 'is_primary', 'assignment_type'])
            ->withTimestamps();
    }

    /**
     * Scope: hanya kampus yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
