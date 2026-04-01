<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SmkDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Admin operator (fixed 1 akun)
        User::factory()->operatorAdmin()->create([
            'password' => Hash::make('password'),
        ]);

        // 2) Guru SMK (fixed 24 guru, semua terverifikasi)
        $gurus = User::factory()
            ->count(24)
            ->guruBelajar()
            ->create();

        // 3) Kelas ideal SMK (fixed 12 kelas: X-XII, 2 rombel per jurusan)
        $kelasNames = [
            'X RPL 1',
            'X RPL 2',
            'XI RPL 1',
            'XI RPL 2',
            'XII RPL 1',
            'XII RPL 2',
            'X TKJ 1',
            'X TKJ 2',
            'XI TKJ 1',
            'XI TKJ 2',
            'XII TKJ 1',
            'XII TKJ 2',
        ];

        $kelasList = collect();
        foreach ($kelasNames as $index => $kelasName) {
            $waliKelasId = $gurus[$index % $gurus->count()]->id;

            $kelas = Kelas::create([
                'name' => $kelasName,
                'wali_kelas_id' => $waliKelasId,
            ]);

            $kelasList->push($kelas);
        }

        // 4) Mapel per kelas (fixed 10 mapel/kelas, total 120)
        $mapelTemplate = [
            'Pendidikan Agama',
            'PPKn',
            'Bahasa Indonesia',
            'Matematika',
            'Bahasa Inggris',
            'Informatika',
            'Produktif Kejuruan Dasar',
            'Produktif Kejuruan Lanjut',
            'Kewirausahaan',
            'Projek P5',
        ];

        foreach ($kelasList as $kelas) {
            foreach ($mapelTemplate as $i => $namaMapel) {
                $kodeMapel = sprintf('MPL-%02d-%02d', $kelas->id, $i + 1);

                $mapel = Mapel::create([
                    'kode_mapel' => $kodeMapel,
                    'nama_mapel' => $namaMapel,
                    'kelas_id' => $kelas->id,
                ]);

                // Assign 2 guru pengampu per mapel secara deterministik
                $firstGuruId = $gurus[($kelas->id + $i) % $gurus->count()]->id;
                $secondGuruId = $gurus[($kelas->id + $i + 7) % $gurus->count()]->id;
                $mapel->gurus()->attach([$firstGuruId, $secondGuruId]);
            }
        }

        // 5) Siswa fixed 100 orang + akun user terverifikasi
        for ($i = 1; $i <= 100; $i++) {
            $nis = sprintf('26%06d', $i);

            $user = User::factory()->siswaBelajar()->create([
                'name' => 'Siswa ' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'email' => 'siswa' . $i . '@siswa.belajar.id',
                'password' => Hash::make('password'),
            ]);

            $kelas = $kelasList[($i - 1) % $kelasList->count()];

            Siswa::create([
                'name' => $user->name,
                'nis' => $nis,
                'kelas_id' => $kelas->id,
                'user_id' => $user->id,
                'phone' => '08' . str_pad((string) (811000000 + $i), 10, '0', STR_PAD_LEFT),
                'address' => 'Alamat siswa ' . $i,
            ]);
        }
    }
}