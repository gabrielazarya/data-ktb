<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Nama tabel di database
     */
    protected $table = 'users';

    /**
     * Primary key kustom
     */
    protected $primaryKey = 'user_id';

    /**
     * Field yang digunakan untuk autentikasi (bukan email, tapi username)
     */
    protected $username = 'username';

    /**
     * Override: field identifier untuk autentikasi Laravel
     */
    public function getAuthIdentifierName(): string
    {
        return 'user_id';
    }


    /**
     * Kolom yang boleh diisi secara massal
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'password',
        'nama_lengkap',
        'tanggal_lahir',
        'kampus_id',
        'regio_id',
        'jurusan',
        'kategori_jurusan_id',
        'angkatan',
        'role',
        'pkk_id',
        'foto_profil',
        'is_target',
        'admin_tipe',
        'is_active',
    ];

    /**
     * Kolom yang disembunyikan dari serialisasi (JSON/array)
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting tipe data kolom
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'password'      => 'hashed',
            'is_target'     => 'boolean',
            'is_active'     => 'boolean',
        ];
    }

    // =========================================================
    // Helper Role — cek role user saat ini
    // =========================================================

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPKK(): bool
    {
        return $this->role === 'pkk';
    }

    public function isAKK(): bool
    {
        return $this->role === 'akk';
    }

    public function isAdminEditor(): bool
    {
        return $this->role === 'admin' && $this->admin_tipe === 'editor';
    }

    public function isAdminPelihat(): bool
    {
        return $this->role === 'admin' && $this->admin_tipe === 'pelihat';
    }

    // =========================================================
    // Relasi Eloquent
    // =========================================================

    /**
     * Kampus asal user ini
     */
    public function kampus()
    {
        return $this->belongsTo(Kampus::class, 'kampus_id', 'kampus_id');
    }

    /**
     * Regio (wilayah) user ini — Surabaya / Malang / dst
     */
    public function regio()
    {
        return $this->belongsTo(Regio::class, 'regio_id', 'regio_id');
    }

    /**
     * Kategori jurusan user ini
     */
    public function kategoriJurusan()
    {
        return $this->belongsTo(KategoriJurusan::class, 'kategori_jurusan_id', 'kategori_jurusan_id');
    }

    /**
     * PKK yang memimpin AKK ini
     */
    public function pkkLeader()
    {
        return $this->belongsTo(User::class, 'pkk_id', 'user_id');
    }

    /**
     * Daftar AKK yang dipimpin oleh PKK ini
     */
    public function akkMembers()
    {
        return $this->hasMany(User::class, 'pkk_id', 'user_id');
    }
}
