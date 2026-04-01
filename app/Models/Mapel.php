<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Mapel extends Model
{
    use HasFactory;

    protected $fillable = ['kode_mapel', 'nama_mapel', 'kelas_id'];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function gurus()
    {
        return $this->belongsToMany(User::class, 'guru_mapel', 'mapel_id', 'guru_id');
    }
}