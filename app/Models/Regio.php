<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Regio extends Model
{
    protected $table      = 'regios';
    protected $primaryKey = 'regio_id';

    protected $fillable = [
        'nama_regio',
        'keterangan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Semua user yang termasuk regio ini
     */
    public function users()
    {
        return $this->hasMany(User::class, 'regio_id', 'regio_id');
    }

    public function kampus()
    {
        return $this->hasMany(Kampus::class, 'regio_id', 'regio_id');
    }

    public function kelompokPemuridan()
    {
        return $this->hasMany(KelompokPemuridan::class, 'regio_id', 'regio_id');
    }
}
