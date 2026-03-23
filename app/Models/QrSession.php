<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QrSession extends Model
{
    use HasFactory;

    protected $table = 'qr_sessions';

    protected $fillable = [
        'kelas_id',
        'session_id',
        'active',
        'started_at',
        'expired_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'started_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->session_id)) {
                $model->session_id = (string) Str::uuid();
            }
        });
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function presensi()
    {
        return $this->hasMany(Presensi::class, 'qr_session_id');
    }

    public function isActive()
    {
        return $this->active && now()->lt($this->expired_at);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true)->where('expired_at', '>', now());
    }

    public function scopeCleanupExpired($query)
    {
        return $query->where('active', true)->where('expired_at', '<', now())->update(['active' => false]);
    }
}
