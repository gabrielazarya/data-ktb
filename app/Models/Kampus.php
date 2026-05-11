<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kampus extends Model
{
    use HasFactory;

    protected $table      = 'kampus';
    protected $primaryKey = 'kampus_id';

    protected $fillable = [
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

    /**
     * Scope: hanya kampus yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
