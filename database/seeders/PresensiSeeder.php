<?php

namespace Database\Seeders;

use App\Models\Presensi;
use App\Models\PresensiSession;
use App\Models\QrSession;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PresensiSeeder extends Seeder
{
    public function run(): void
    {
        $today = today();
        $siswa = Siswa::all();

        // Get or create a PresensiSession and QrSession for today
        $presensiSession = PresensiSession::firstOrCreate(
            [
                'kelas_id' => 2,
                'is_active' => false,
                'started_at' => $today->startOfDay(),
            ],
            [
                'guru_id' => 1,
                'session_token' => Str::random(40),
                'ended_at' => $today->endOfDay(),
            ]
        );

        $qrSession = QrSession::firstOrCreate(
            [
                'kelas_id' => 2,
                'started_at' => $today->startOfDay(),
            ],
            [
                'session_id' => (string) Str::uuid(),
                'active' => false,
                'expired_at' => $today->endOfDay(),
            ]
        );

        foreach ($siswa->random(min(20, $siswa->count())) as $student) {
            Presensi::create([
                'siswa_id' => $student->id,
                'session_id' => $presensiSession->id,
                'qr_session_id' => $qrSession->id,
                'tanggal' => $today,
                'waktu' => Carbon::now()->subMinutes(rand(0, 60))->format('H:i:s'),
                'waktu_scan' => Carbon::now()->subMinutes(rand(0, 60)),
                'status' => rand(0, 1) ? 'hadir' : 'terlambat',
            ]);
        }
    }
}
