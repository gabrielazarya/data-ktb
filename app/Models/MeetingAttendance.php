<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingAttendance extends Model
{
    use HasFactory;

    protected $table = 'meeting_attendances';

    protected $primaryKey = 'attendance_id';

    protected $fillable = [
        'meeting_id',
        'laporan_id',
        'user_id',
        'status',
        'recorded_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
        ];
    }

    public function meeting()
    {
        return $this->belongsTo(LaporanPertemuanKelompok::class, 'meeting_id', 'laporan_id');
    }

    public function laporan()
    {
        return $this->belongsTo(LaporanPertemuanKelompok::class, 'laporan_id', 'laporan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function isPresent(): bool
    {
        return $this->status === 'hadir';
    }
}
