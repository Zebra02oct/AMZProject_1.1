<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nis',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'tokens.*',
    ];

    public function isAdmin(): bool
    {
        return strcasecmp((string) $this->role, 'Admin') === 0;
    }

    public function isGuru(): bool
    {
        return strcasecmp((string) $this->role, 'Guru') === 0;
    }

    public function isSiswa(): bool
    {
        return strcasecmp((string) $this->role, 'Siswa') === 0;
    }

    public function initials(): string
    {
        return (string) Str::of($this->name ?? '')
            ->trim()
            ->explode(' ')
            ->filter()
            ->map(fn (string $part) => Str::substr($part, 0, 1))
            ->take(2)
            ->implode('');
    }

    public function siswa()
    {
        return $this->hasOne(Siswa::class, 'user_id');
    }

    // Relasi ke tabel Kelas (Sebagai Wali Kelas)
    public function waliKelas()
    {
        return $this->hasOne(Kelas::class, 'wali_kelas_id');
    }

    // Relasi ke tabel Mapel (Banyak Guru bisa ajar Banyak Mapel)
    public function mapels()
    {
        return $this->belongsToMany(Mapel::class, 'guru_mapel', 'guru_id', 'mapel_id');
    }

    // public function siswa()
    // {
    //     return $this->hasOne(Siswa::class);
    // }
}
