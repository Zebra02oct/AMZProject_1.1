<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Database\Seeder;

class SiswaSeeder extends Seeder
{
    public function run(): void
    {
        $kelas = Kelas::all();

        $names = ['Ahmad', 'Budi', 'Citra', 'Dewi', 'Eko', 'Fani', 'Gina', 'Hadi', 'Indra', 'Joko'];

        foreach ($kelas as $k) {
            foreach ($names as $name) {
                Siswa::create(['name' => $name.' ('.$k->name.')', 'nis' => 'NIS'.rand(10000, 99999), 'kelas_id' => $k->id]);
            }
        }
    }
}
