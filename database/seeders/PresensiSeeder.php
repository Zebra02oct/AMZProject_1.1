<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Presensi;
use App\Models\Siswa;
use Carbon\Carbon;

class PresensiSeeder extends Seeder
{
    public function run(): void
    {
        $today = today();
        $siswa = Siswa::all();

        foreach ($siswa->random(20) as $siswa) {
            Presensi::create([
                'siswa_id' => $siswa->id,
                'tanggal' => $today,
                'waktu' => \Carbon\Carbon::now()->subMinutes(rand(0, 60))->format('H:i:s'),
                'status' => rand(0, 1) ? 'hadir' : 'terlambat',
            ]);
        }
    }
}
