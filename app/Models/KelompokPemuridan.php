<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KelompokPemuridan extends Model
{
    use HasFactory;

    protected $table = 'kelompok_pemuridan';
    protected $primaryKey = 'kelompok_id';

    protected $fillable = [
        'nama_kelompok',
        'kampus_id',
        'regio_id',
        'pemimpin_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function kampus()
    {
        return $this->belongsTo(Kampus::class, 'kampus_id', 'kampus_id');
    }

    public function pemimpin()
    {
        return $this->belongsTo(User::class, 'pemimpin_id', 'user_id');
    }

    public function regio()
    {
        return $this->belongsTo(Regio::class, 'regio_id', 'regio_id');
    }

    public function anggota()
    {
        return $this->hasMany(User::class, 'kelompok_id', 'kelompok_id');
    }

    public function laporanPertemuan()
    {
        return $this->hasMany(LaporanPertemuanKelompok::class, 'kelompok_id', 'kelompok_id');
    }
}
