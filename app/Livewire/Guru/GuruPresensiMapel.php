<?php

namespace App\Livewire\Guru;

use App\Models\Mapel;
use App\Models\Presensi;
use App\Models\PresensiSession;
use App\Models\Siswa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class GuruPresensiMapel extends Component
{
    public $selectedMapelId = '';
    public $latitude = null;
    public $longitude = null;
    public $activeSession = null;
    public $currentPresensi = [];
    public $totalSiswaInKelas = 0;
    public $hadirCount = 0;
    public $terlambatCount = 0;
    public $belumHadirCount = 0;
    public $qrData = '';
    public $sessionCountdown = 15 * 60;
    public $qrCountdown = 5 * 60;
    public $isSessionActive = false;
    public $mapelList = [];

    public function mount()
    {
        $this->loadMapelList();
        $this->loadData();
    }

    public function loadMapelList()
    {
        $this->mapelList = Mapel::whereHas('gurus', function ($query) {
            $query->where('guru_id', Auth::id());
        })
            ->with('kelas')
            ->orderBy('nama_mapel')
            ->get();
    }

    public function startSession()
    {
        PresensiSession::cleanupExpired();

        if (!$this->selectedMapelId) {
            $this->addError('selectedMapelId', 'Pilih mata pelajaran terlebih dahulu.');
            return;
        }

        $mapel = Mapel::with('kelas')->find($this->selectedMapelId);

        if (!$mapel) {
            $this->dispatch('swal-error', message: 'Mata pelajaran tidak ditemukan.');
            return;
        }

        $isGuruPengampu = $mapel->gurus()->where('guru_id', Auth::id())->exists();
        if (!$isGuruPengampu) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki akses ke mata pelajaran ini.');
            return;
        }

        if (($this->latitude !== null && $this->longitude === null) || ($this->latitude === null && $this->longitude !== null)) {
            $this->dispatch('swal-error', message: 'Data lokasi tidak lengkap. Silakan coba mulai ulang sesi.');
            return;
        }

        if ($this->latitude !== null && $this->longitude !== null) {
            if (!is_numeric($this->latitude) || !is_numeric($this->longitude)) {
                $this->dispatch('swal-error', message: 'Format koordinat tidak valid.');
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
        }

        $hasActiveMapelSession = PresensiSession::guruActive(Auth::id())
            ->where('tipe_sesi', 'mapel')
            ->exists();

        if ($hasActiveMapelSession) {
            $this->dispatch('swal-error', message: 'Masih ada sesi presensi mapel yang aktif.');
            return;
        }

        $this->activeSession = PresensiSession::create([
            'kelas_id' => $mapel->kelas_id,
            'guru_id' => Auth::id(),
            'tipe_sesi' => 'mapel',
            'mapel_id' => $mapel->id,
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
        $this->dispatch('render-qr-mapel');
        $this->dispatch('swal-success', message: 'Sesi presensi mata pelajaran dimulai.');
    }

    public function refreshQr()
    {
        if (!$this->activeSession) {
            return;
        }

        $newToken = Str::random(40);
        $this->activeSession->update(['session_token' => $newToken]);
        $this->qrData = $newToken;
        $this->qrCountdown = 5 * 60;
        $this->dispatch('render-qr-mapel');
    }

    public function closeSession()
    {
        if (!$this->activeSession) {
            return;
        }

        $this->storeTidakHadirForSession(
            $this->activeSession->id,
            $this->activeSession->kelas_id,
            $this->activeSession->mapel_id
        );

        $this->activeSession->update([
            'is_active' => false,
            'ended_at' => now(),
        ]);

        $this->resetSessionState();
        $this->loadData();
        $this->dispatch('swal-success', message: 'Sesi presensi mata pelajaran ditutup.');
    }

    public function loadData()
    {
        $this->activeSession = PresensiSession::guruActive(Auth::id())
            ->where('tipe_sesi', 'mapel')
            ->with(['kelas', 'mapel'])
            ->first();

        if (!$this->activeSession) {
            $this->resetSessionState();
            return;
        }

        $this->selectedMapelId = (string) $this->activeSession->mapel_id;
        $this->totalSiswaInKelas = Siswa::where('kelas_id', $this->activeSession->kelas_id)->count();

        $this->currentPresensi = Presensi::with('siswa')
            ->where('session_id', $this->activeSession->id)
            ->where('tipe_sesi', 'mapel')
            ->whereIn('status', ['hadir', 'terlambat'])
            ->orderBy('waktu_scan', 'desc')
            ->get();

        $this->calculateStats();
        $this->qrData = $this->activeSession->session_token;
        $this->isSessionActive = true;

        $startedAt = $this->activeSession->started_at;
        $this->sessionCountdown = max(0, 15 * 60 - $startedAt->diffInSeconds(now()));
        $this->dispatch('render-qr-mapel');
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

            if ($scanSeconds <= 300) {
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

    private function storeTidakHadirForSession(int $sessionId, int $kelasId, ?int $mapelId): void
    {
        $siswaIds = Siswa::where('kelas_id', $kelasId)->pluck('id');
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
        $rows = $missingSiswaIds->map(function ($siswaId) use ($sessionId, $mapelId, $now) {
            return [
                'siswa_id' => $siswaId,
                'session_id' => $sessionId,
                'qr_session_id' => null,
                'tipe_sesi' => 'mapel',
                'mapel_id' => $mapelId,
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
        return view('livewire.guru.guru-presensi-mapel');
    }
}
