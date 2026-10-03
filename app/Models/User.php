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

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            if ($user->isStaff()) {
                $user->kampus_id = null;
            }
        });
    }

    /**
     * Override: field identifier untuk session (primary key)
     * CATATAN: Ini TIDAK mengubah field login — login tetap pakai 'username'
     */
    public function getAuthIdentifier()
    {
        return $this->getAttribute($this->primaryKey); // user_id
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
        'kelompok_id',
        'foto_profil',
        'admin_tipe',
        'is_active',
        'lifecycle_status',
        'lifecycle_changed_at',
        'lifecycle_reason',
        'must_change_password',
        'last_login_at',
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
            'password' => 'hashed',
            'is_active' => 'boolean',
            'lifecycle_changed_at' => 'datetime',
            'must_change_password' => 'boolean',
            'last_login_at' => 'datetime',
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

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function canLeadGroups(): bool
    {
        return in_array($this->role, ['pkk', 'staff'], true);
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

    public function kelompokPemuridan()
    {
        return $this->belongsTo(KelompokPemuridan::class, 'kelompok_id', 'kelompok_id');
    }

    public function kelompokDipimpin()
    {
        return $this->hasMany(KelompokPemuridan::class, 'pemimpin_id', 'user_id');
    }

    public function laporanPertemuan()
    {
        return $this->hasMany(LaporanPertemuanKelompok::class, 'pkk_id', 'user_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'actor_user_id', 'user_id');
    }

    /**
     * Daftar AKK yang dipimpin oleh PKK ini
     */
    public function akkMembers()
    {
        return $this->hasMany(User::class, 'pkk_id', 'user_id');
    }

    /**
     * Historical and current group memberships. The legacy kelompok_id
     * relation remains available while callers migrate to these relations.
     */
    public function groupMemberships()
    {
        return $this->hasMany(GroupMembership::class, 'user_id', 'user_id');
    }

    public function currentGroupMemberships()
    {
        return $this->groupMemberships()->active();
    }

    public function groups()
    {
        return $this->belongsToMany(
            KelompokPemuridan::class,
            'group_memberships',
            'user_id',
            'kelompok_id',
            'user_id',
            'kelompok_id'
        )->withPivot(['membership_id', 'role', 'status', 'started_at', 'ended_at', 'reason', 'notes'])
            ->withTimestamps();
    }

    public function mentorshipsAsMentor()
    {
        return $this->hasMany(Mentorship::class, 'mentor_id', 'user_id');
    }

    public function mentorshipsAsMentee()
    {
        return $this->hasMany(Mentorship::class, 'mentee_id', 'user_id');
    }

    public function activeMentorshipsAsMentor()
    {
        return $this->mentorshipsAsMentor()->active();
    }

    public function activeMentorshipsAsMentee()
    {
        return $this->mentorshipsAsMentee()->active();
    }

    public function groupLeadershipHistory()
    {
        return $this->hasMany(GroupLeaderHistory::class, 'leader_id', 'user_id');
    }

    public function isLifecycleActive(): bool
    {
        return ($this->lifecycle_status ?: 'active') === 'active' && (bool) $this->is_active;
    }

    public function scopeLifecycleActive($query)
    {
        return $query->where('lifecycle_status', 'active')->where('is_active', true);
    }
}
