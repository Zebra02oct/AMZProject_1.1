<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    protected $table = 'presensi';

    use HasFactory;

    protected $fillable = ['siswa_id', 'session_id', 'qr_session_id', 'tanggal', 'waktu_scan', 'waktu', 'status'];

    protected $casts = [
        'tanggal' => 'date',
        'waktu' => 'datetime:H:i',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function session()
    {
        return $this->belongsTo(PresensiSession::class, 'session_id');
    }

    public function qrSession()
    {
        return $this->belongsTo(QrSession::class, 'qr_session_id');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('tanggal', today());
    }

    public function scopeHadir($query)
    {
        return $query->where('status', 'hadir');
    }

    public function scopeDateRange($query, $dateFrom, $dateTo)
    {
        if ($dateFrom) {
            $query->whereDate('tanggal', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('tanggal', '<=', $dateTo);
        }
        return $query;
    }

    public function scopeKelasFilter($query, $kelasId)
    {
        if ($kelasId) {
            $query->whereHas('siswa', function ($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            });
        }
        return $query;
    }
}