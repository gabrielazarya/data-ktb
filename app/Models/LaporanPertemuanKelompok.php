<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanPertemuanKelompok extends Model
{
    use HasFactory;

    protected $table = 'laporan_pertemuan_kelompok';
    protected $primaryKey = 'laporan_id';

    protected $fillable = [
        'kelompok_id',
        'pkk_id',
        'tanggal_pertemuan',
        'pertemuan_ke',
        'bahan',
        'ringkasan',
        'anggota_hadir',
        'jumlah_hadir',
        'catatan',
        'rencana_lanjutan',
        'kendala',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pertemuan' => 'date',
            'anggota_hadir' => 'array',
        ];
    }

    public function kelompok()
    {
        return $this->belongsTo(KelompokPemuridan::class, 'kelompok_id', 'kelompok_id');
    }

    public function pelapor()
    {
        return $this->belongsTo(User::class, 'pkk_id', 'user_id');
    }
}
