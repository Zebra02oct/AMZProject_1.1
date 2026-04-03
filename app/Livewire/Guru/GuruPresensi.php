<?php

namespace App\Livewire\Guru;

use App\Models\Kelas;
use App\Models\Presensi;
use App\Models\PresensiSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class GuruPresensi extends Component
{
    public $latitude = null;
    public $longitude = null;
    public $activeSession = null;
    public $currentPresensi = [];
    public $totalSiswaInKelas = 0;
    public $hadirCount = 0;
    public $terlambatCount = 0;
    public $belumHadirCount = 0;
    public $qrData = '';
    public $sessionCountdown = 15 * 60; // 15 minutes in seconds
    public $qrCountdown = 5 * 60; // 5 minutes in seconds
    public $isSessionActive = false;
    public $guruKelas = null;

    public function mount()
    {
        $this->loadGuruKelas();
        $this->loadData();
        $this->dispatch('poll-interval', interval: 3000);
    }

    public function loadGuruKelas()
    {
        // Ambil kelas dimana guru ini adalah wali kelas
        $this->guruKelas = Kelas::where('wali_kelas_id', Auth::id())->with('siswa')->first();
    }

    public function startSession()
    {
        PresensiSession::cleanupExpired();

        if (!$this->guruKelas) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki kelas yang ditugaskan sebagai wali kelas!');
            return;
        }

        // Sementara testing: lokasi wajib dari browser/laptop, jangan lanjut jika kosong.
        if ($this->latitude === null || $this->longitude === null) {
            $this->dispatch('swal-error', message: 'Lokasi belum terdeteksi. Aktifkan izin lokasi browser lalu coba lagi.');
            return;
        }

        if (!is_numeric($this->latitude) || !is_numeric($this->longitude)) {
            $this->dispatch('swal-error', message: 'Format koordinat lokasi tidak valid.');
            return;
        }

        $latitude = (float) $this->latitude;
        $longitude = (float) $this->longitude;

        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            $this->dispatch('swal-error', message: 'Koordinat lokasi di luar rentang yang valid.');
            return;
        }

        $this->latitude = $latitude;
        $this->longitude = $longitude;

        // Manual host location mode dinonaktifkan sementara untuk testing.
        // Jika ingin aktifkan lagi, kembalikan fallback null/null di sini.

        if (PresensiSession::guruActive(Auth::id())
            ->where('tipe_sesi', 'harian')
            ->where('kelas_id', $this->guruKelas->id)
            ->exists()
        ) {
            $this->dispatch('swal-error', message: 'Sesi aktif sudah berlangsung untuk kelas ini!');
            return;
        }

        $this->activeSession = PresensiSession::create([
            'kelas_id' => $this->guruKelas->id,
            'guru_id' => Auth::id(),
            'tipe_sesi' => 'harian',
            'mapel_id' => null,
            'session_token' => Str::random(40),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'started_at' => now(),
            'is_active' => true,
        ]);

        $this->qrData = $this->activeSession->session_token;
        $this->isSessionActive = true;
        $this->sessionCountdown = 15 * 60;
        $this->qrCountdown = 5 * 60;
        $this->loadData();
        $this->dispatch('render-qr-harian');
        $this->dispatch('swal-success', message: 'Sesi presensi dimulai!');
    }

    public function refreshQr()
    {
        if (!$this->activeSession) return;

        // Update token for new QR (same session)
        $newToken = Str::random(40);
        $this->activeSession->update(['session_token' => $newToken]);
        $this->qrData = $newToken;
        $this->qrCountdown = 5 * 60; // Reset QR timer
        $this->dispatch('render-qr-harian');
        $this->dispatch('qr-refreshed', qrData: $this->qrData);
    }

    public function closeSession()
    {
        if ($this->activeSession) {
            $this->storeTidakHadirForSession($this->activeSession->id, $this->activeSession->kelas_id);

            $this->activeSession->update([
                'is_active' => false,
                'ended_at' => now()
            ]);
            $this->resetSessionState();
            $this->loadData();
            $this->dispatch('swal-success', message: 'Sesi presensi ditutup!');
        }
    }

    public function loadData()
    {
        $guruId = Auth::id();

        // Cek apakah ada sesi aktif untuk kelas guru
        if ($this->guruKelas) {
            $this->activeSession = PresensiSession::guruActive($guruId)
                ->where('tipe_sesi', 'harian')
                ->where('kelas_id', $this->guruKelas->id)
                ->with('kelas')
                ->first();

            if ($this->activeSession) {
                $this->totalSiswaInKelas = $this->guruKelas->siswa()->count();

                $this->currentPresensi = Presensi::with('siswa')
                    ->where('session_id', $this->activeSession->id)
                    ->where('tipe_sesi', 'harian')
                    ->whereIn('status', ['hadir', 'terlambat'])
                    ->orderBy('waktu_scan', 'desc')
                    ->get();

                $this->calculateStats();
                $this->qrData = $this->activeSession->session_token;
                $this->isSessionActive = true;

                // Calculate remaining time
                $startedAt = $this->activeSession->started_at;
                $this->sessionCountdown = max(0, 15 * 60 - $startedAt->diffInSeconds(now()));
                $this->dispatch('render-qr-harian');
            } else {
                $this->isSessionActive = false;
                $this->qrData = '';
                $this->currentPresensi = [];
            }
        } else {
            $this->isSessionActive = false;
            $this->qrData = '';
            $this->currentPresensi = [];
        }
    }

    private function calculateStats()
    {
        $this->hadirCount = 0;
        $this->terlambatCount = 0;
        $this->belumHadirCount = $this->totalSiswaInKelas;

        foreach ($this->currentPresensi as $presensi) {
            if (!$presensi instanceof Presensi) {
                continue;
            }

            $scanSeconds = 0;
            if ($presensi->waktu_scan) {
                $scanSeconds = abs($presensi->waktu_scan->getTimestamp() - $this->activeSession->started_at->getTimestamp());
            }

            if ($scanSeconds <= 300) { // 5 min
                $this->hadirCount++;
            } else {
                $this->terlambatCount++;
            }
            $this->belumHadirCount--;
        }
    }

    private function resetSessionState()
    {
        $this->activeSession = null;
        $this->qrData = '';
        $this->isSessionActive = false;
        $this->sessionCountdown = 0;
        $this->qrCountdown = 0;
        $this->hadirCount = 0;
        $this->terlambatCount = 0;
        $this->belumHadirCount = 0;
        $this->currentPresensi = [];
    }

    private function storeTidakHadirForSession(int $sessionId, int $kelasId): void
    {
        $siswaIds = Kelas::whereKey($kelasId)
            ->with('siswa:id')
            ->first()?->siswa
            ->pluck('id')
            ->values() ?? collect();

        if ($siswaIds->isEmpty()) {
            return;
        }

        $existingSiswaIds = Presensi::where('session_id', $sessionId)
            ->pluck('siswa_id');

        $missingSiswaIds = $siswaIds->diff($existingSiswaIds)->values();
        if ($missingSiswaIds->isEmpty()) {
            return;
        }

        $now = now();
        $rows = $missingSiswaIds->map(function ($siswaId) use ($sessionId, $now) {
            return [
                'siswa_id' => $siswaId,
                'session_id' => $sessionId,
                'qr_session_id' => null,
                'tipe_sesi' => 'harian',
                'mapel_id' => null,
                'tanggal' => $now->toDateString(),
                'waktu_scan' => null,
                'waktu' => $now->format('H:i'),
                'status' => 'tidak_hadir',
                'keterangan' => 'tanpa_keterangan',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->all();

        Presensi::insert($rows);
    }

    public function render()
    {
        return view('livewire.guru.guru-presensi');
    }
}
