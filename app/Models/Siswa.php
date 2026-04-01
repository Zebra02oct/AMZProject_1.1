<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswa';

    use HasFactory;

    protected $fillable = [
        'name',
        'nis',
        'kelas_id',
        'user_id',
        'phone',
        'address',
    ];

    public function scopeSearch($query, $search)
    {
        return $search ? $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('nis', 'like', "%{$search}%");
        }) : $query;
    }

    public function scopeKelas($query, $kelasId)
    {
        return $kelasId ? $query->where('kelas_id', $kelasId) : $query;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke Kelas
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    // Relasi ke Presensi
    public function presensi()
    {
        return $this->hasMany(Presensi::class, 'siswa_id');
    }
}