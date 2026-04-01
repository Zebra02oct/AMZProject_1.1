<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';

    use HasFactory;

    protected $fillable = ['name', 'wali_kelas_id'];

    public function siswa()
    {
        return $this->hasMany(Siswa::class);
    }
    // Relasi ke Guru yang menjadi Wali Kelas
    public function waliKelas()
    {
        return $this->belongsTo(User::class, 'wali_kelas_id');
    }

    public function mapels()
    {
        return $this->hasMany(Mapel::class);
    }
}
