<?php

namespace App\Livewire\Guru;

use App\Models\Kelas;
use App\Models\Presensi;
use App\Models\PresensiSession;
use App\Models\Siswa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class GuruPresensi extends Component
{
    public $selectedKelasId = '';
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

    public $kelasList = [];

    public function mount()
    {
        $this->loadKelasList();
        $this->loadData();
        $this->dispatch('poll-interval', interval: 3000);
    }

    public function loadKelasList()
    {
        // For now, all kelas with siswa; ideally filter by guru-taught kelas
        $this->kelasList = Kelas::whereHas('siswa')->pluck('name', 'id')->toArray();
    }

    public function updatedSelectedKelasId($value)
    {
        if ($value) {
            $this->checkActiveSession();
        }
    }

    public function checkActiveSession()
    {
        $this->activeSession = PresensiSession::guruActive(Auth::id())
            ->where('kelas_id', $this->selectedKelasId)
            ->with('kelas')
            ->first();

        if ($this->activeSession) {
            $this->isSessionActive = true;
            $this->qrData = $this->activeSession->session_token;
            $this->totalSiswaInKelas = Siswa::where('kelas_id', $this->selectedKelasId)->count();
            $this->currentPresensi = Presensi::with('siswa')
                ->where('session_id', $this->activeSession->id)
                ->orderBy('waktu_scan', 'desc')
                ->get();
            $this->calculateStats();
            $startedAt = $this->activeSession->started_at;
            $this->sessionCountdown = max(0, 15 * 60 - $startedAt->diffInSeconds(now()));
        } else {
            $this->resetSessionState();
        }
    }

    public function startSession()
    {
        PresensiSession::cleanupExpired();

        if (!$this->selectedKelasId) {
            $this->addError('selectedKelasId', 'Pilih kelas terlebih dahulu!');
            return;
        }

        if (PresensiSession::guruActive(Auth::id())
            ->where('kelas_id', $this->selectedKelasId)
            ->exists()
        ) {
            session()->flash('error', 'Sesi aktif sudah berlangsung untuk kelas ini!');
            return;
        }

        $this->activeSession = PresensiSession::create([
            'kelas_id' => $this->selectedKelasId,
            'guru_id' => Auth::id(),
            'session_token' => Str::random(40),
            'started_at' => now(),
            'is_active' => true,
        ]);

        $this->qrData = $this->activeSession->session_token;
        $this->isSessionActive = true;
        $this->sessionCountdown = 15 * 60;
        $this->qrCountdown = 5 * 60;
        $this->loadData();
        session()->flash('message', 'Sesi presensi dimulai!');
    }

    public function refreshQr()
    {
        if (!$this->activeSession) return;

        // Update token for new QR (same session)
        $newToken = Str::random(40);
        $this->activeSession->update(['session_token' => $newToken]);
        $this->qrData = $newToken;
        $this->qrCountdown = 5 * 60; // Reset QR timer
        $this->dispatch('qr-refreshed', qrData: $this->qrData);
    }

    public function closeSession()
    {
        if ($this->activeSession) {
            $this->activeSession->update([
                'is_active' => false,
                'ended_at' => now()
            ]);
            $this->resetSessionState();
            $this->loadData();
            session()->flash('message', 'Sesi presensi ditutup!');
        }
    }

    public function loadData()
    {
        $guruId = Auth::id();
        $this->activeSession = PresensiSession::guruActive($guruId)
            ->with('kelas')
            ->first();

        if ($this->activeSession) {
            $kelasId = $this->activeSession->kelas_id;
            $this->selectedKelasId = $kelasId;
            $this->totalSiswaInKelas = Siswa::where('kelas_id', $kelasId)->count();

            $this->currentPresensi = Presensi::with('siswa')
                ->where('session_id', $this->activeSession->id)
                ->orderBy('waktu_scan', 'desc')
                ->get();

            $this->calculateStats();
            $this->qrData = $this->activeSession->session_token;
            $this->isSessionActive = true;

            // Calculate remaining time
            $startedAt = $this->activeSession->started_at;
            $this->sessionCountdown = max(0, 15 * 60 - $startedAt->diffInSeconds(now()));
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
            $scanSeconds = $presensi->waktu_scan?->diffInSeconds($this->activeSession->started_at) ?? 0;
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

    public function render()
    {
        return view('livewire.guru.guru-presensi');
    }
}
