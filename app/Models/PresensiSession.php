<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresensiSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'kelas_id',
        'guru_id',
        'session_token',
        'started_at',
        'ended_at',
        'is_active',
        'latitude',
        'longitude',
        'tipe_sesi',
        'mapel_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function presensis()
    {
        return $this->hasMany(Presensi::class, 'session_id');
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('started_at', '>=', now()->subMinutes(15));
    }

    public function scopeGuruActive($query, $guruId)
    {
        return $query->where('guru_id', $guruId)->active();
    }

    public static function cleanupExpired()
    {
        self::where('is_active', true)
            ->where('started_at', '<', now()->subMinutes(15))
            ->update([
                'is_active' => false,
                'ended_at' => now()
            ]);
    }
}
