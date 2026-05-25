<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriJurusan extends Model
{
    protected $table      = 'kategori_jurusan';
    protected $primaryKey = 'kategori_jurusan_id';

    protected $fillable = [
        'nama_kategori',
        'keterangan',
    ];

    /**
     * Semua user yang memiliki kategori jurusan ini
     */
    public function users()
    {
        return $this->hasMany(User::class, 'kategori_jurusan_id', 'kategori_jurusan_id');
    }
}
