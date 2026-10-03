<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupCampus extends Model
{
    use HasFactory;

    protected $table = 'group_campuses';

    protected $primaryKey = 'group_campus_id';

    protected $fillable = [
        'kelompok_id',
        'kampus_id',
        'is_primary',
        'assignment_type',
    ];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }

    public function kelompok()
    {
        return $this->belongsTo(KelompokPemuridan::class, 'kelompok_id', 'kelompok_id');
    }

    public function kampus()
    {
        return $this->belongsTo(Kampus::class, 'kampus_id', 'kampus_id');
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}
