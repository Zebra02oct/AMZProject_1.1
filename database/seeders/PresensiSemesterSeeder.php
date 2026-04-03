<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Presensi;
use App\Models\PresensiSession;
use App\Models\QrSession;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;

class PresensiSemesterSeeder extends Seeder
{
    public function run(): void
    {
        $startDate = now()->subMonthsNoOverflow(6)->startOfDay();
        $endDate = now()->endOfDay();

        $kelasList = Kelas::query()
            ->with(['mapels.gurus', 'waliKelas'])
            ->orderBy('id')
            ->get();

        $siswaByKelas = Siswa::query()
            ->orderBy('kelas_id')
            ->orderBy('id')
            ->get()
            ->groupBy('kelas_id');

        $period = CarbonPeriod::create($startDate, '1 day', $endDate);

        foreach ($period as $date) {
            if (! $date->isMonday() && ! $date->isThursday()) {
                continue;
            }

            /** @var Kelas $kelas */
            foreach ($kelasList as $kelas) {
                $students = $siswaByKelas->get($kelas->id, collect());

                if ($students->isEmpty()) {
                    continue;
                }

                $isHarianSession = $date->isMonday();
                $mapel = $isHarianSession ? null : $this->pickMapelForClass($kelas, $date);

                if (! $isHarianSession && ! $mapel) {
                    continue;
                }

                $startedAt = $this->buildSessionStart($date, $kelas->id, $isHarianSession);
                $sessionKey = implode('|', [
                    $kelas->id,
                    $date->toDateString(),
                    $isHarianSession ? 'harian' : 'mapel',
                    $mapel?->id ?? 'none',
                ]);

                $presensiSession = PresensiSession::updateOrCreate(
                    [
                        'session_token' => sha1('session|'.$sessionKey),
                    ],
                    [
                        'kelas_id' => $kelas->id,
                        'guru_id' => $this->resolveGuruId($kelas, $mapel),
                        'started_at' => $startedAt,
                        'ended_at' => $startedAt->copy()->addHours(2),
                        'is_active' => false,
                        'tipe_sesi' => $isHarianSession ? 'harian' : 'mapel',
                        'mapel_id' => $mapel?->id,
                    ]
                );

                $qrSession = QrSession::updateOrCreate(
                    [
                        'session_id' => sha1('qr|'.$sessionKey),
                    ],
                    [
                        'kelas_id' => $kelas->id,
                        'active' => false,
                        'started_at' => $startedAt,
                        'expired_at' => $startedAt->copy()->addHours(2),
                    ]
                );

                foreach ($students as $student) {
                    $this->seedStudentAttendance($presensiSession, $qrSession, $student, $startedAt, $mapel);
                }
            }
        }
    }

    private function pickMapelForClass(Kelas $kelas, Carbon $date): ?Mapel
    {
        $mapels = $kelas->mapels->values();

        if ($mapels->isEmpty()) {
            return null;
        }

        $index = ($date->weekOfYear + $kelas->id) % $mapels->count();

        return $mapels->get($index);
    }

    private function resolveGuruId(Kelas $kelas, ?Mapel $mapel): int
    {
        if ($mapel?->gurus?->isNotEmpty()) {
            return (int) $mapel->gurus->first()->id;
        }

        if ($kelas->wali_kelas_id) {
            return (int) $kelas->wali_kelas_id;
        }

        return (int) User::query()->orderBy('id')->value('id');
    }

    private function buildSessionStart(Carbon $date, int $kelasId, bool $isHarianSession): Carbon
    {
        $baseHour = $isHarianSession ? 7 : 9;
        $baseMinute = $isHarianSession ? 10 : 30;
        $offset = $kelasId % 5;

        return $date->copy()->setTime($baseHour, $baseMinute + ($offset * 3), 0);
    }

    private function seedStudentAttendance(
        PresensiSession $presensiSession,
        QrSession $qrSession,
        Siswa $student,
        Carbon $startedAt,
        ?Mapel $mapel
    ): void {
        $roll = crc32($presensiSession->session_token.'|'.$student->id) % 100;

        if ($roll < 72) {
            $status = 'hadir';
            $waktuScan = $startedAt->copy()->addMinutes(4 + ($student->id % 7));
            $waktu = $waktuScan->format('H:i:s');
            $keterangan = 'tanpa_keterangan';
        } elseif ($roll < 88) {
            $status = 'terlambat';
            $waktuScan = $startedAt->copy()->addMinutes(16 + ($student->id % 18));
            $waktu = $waktuScan->format('H:i:s');
            $keterangan = 'tanpa_keterangan';
        } else {
            $status = 'tidak_hadir';
            $waktuScan = null;
            $waktu = $startedAt->format('H:i:s');
            $reasonRoll = crc32($presensiSession->session_token.'|reason|'.$student->id) % 100;
            $keterangan = match (true) {
                $reasonRoll < 40 => 'sakit',
                $reasonRoll < 75 => 'izin',
                default => 'tanpa_keterangan',
            };
        }

        Presensi::updateOrCreate(
            [
                'session_id' => $presensiSession->id,
                'siswa_id' => $student->id,
            ],
            [
                'qr_session_id' => $qrSession->id,
                'tanggal' => $startedAt->toDateString(),
                'waktu' => $waktu,
                'waktu_scan' => $waktuScan,
                'status' => $status,
                'keterangan' => $keterangan,
                'tipe_sesi' => $presensiSession->tipe_sesi,
                'mapel_id' => $mapel?->id,
            ]
        );
    }
}
