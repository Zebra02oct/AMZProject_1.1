<?php

namespace App\Livewire\Admin;

use App\Models\Kelas;
use App\Models\PresensiSession;
use App\Models\QrSession;
use App\Models\Presensi;
use App\Models\Siswa;
use Illuminate\Support\Str;
use Livewire\Component;

class AdmPresensi extends Component
{
    public $kelasList = [];
    public $selectedKelasId = '';
    public $activeSession = null;
    public $currentPresensi;
    public $historySessions = [];
    public $qrData = '';
    public $totalSiswaInKelas = 0;
    public $belumPresensi = 0;


    public function mount()
    {
        $this->kelasList = Kelas::pluck('name', 'id')->toArray();
        $this->loadData();
    }

    public function updatedSelectedKelasId()
    {
        // QR generation is handled by Alpine.js generateQrIfSelected() function
        // which calls startSession() when a class is selected
    }

    public function startSession()
    {
        PresensiSession::cleanupExpired();

        if (PresensiSession::active()->where('kelas_id', $this->selectedKelasId)->exists()) {
            session()->flash('error', 'Ada sesi aktif untuk kelas ini! Tutup sesi sebelumnya.');
            return;
        }

        if (!$this->selectedKelasId) {
            session()->flash('error', 'Pilih kelas terlebih dahulu!');
            return;
        }

        $this->activeSession = PresensiSession::create([
            'kelas_id' => $this->selectedKelasId,
            'guru_id' => auth()->id(),
            'session_token' => Str::random(40),
            'started_at' => now(),
            'is_active' => true,
        ]);

        $this->qrData = $this->activeSession->session_token;
        $this->loadData();
        session()->flash('success', 'Sesi presensi dimulai!');
        $this->dispatch('qr-session-started', qrData: $this->qrData);
    }

    public function closeSession()
    {
        if ($this->activeSession) {
            $this->activeSession->update(['is_active' => false, 'ended_at' => now()]);
            $this->activeSession = null;
            $this->qrData = '';
            $this->loadData();

            // Emit event to clear QR code
            $this->dispatch('qr-session-ended');
        }
    }

    public function loadData()
    {
        QrSession::where('active', true)->where('expired_at', '<', now())->update(['active' => false]);

        $this->activeSession = PresensiSession::active()->first();
        $this->qrData = $this->activeSession?->session_token ?? '';

        if ($this->activeSession) {
            $kelasId = $this->activeSession->kelas_id;
            $totalSiswa = Siswa::where('kelas_id', $kelasId)->count();
            $this->totalSiswaInKelas = $totalSiswa;

            $this->currentPresensi = Presensi::with(['siswa.kelas'])
                ->where('session_id', $this->activeSession->id)
                ->orderBy('waktu_scan', 'desc')
                ->get();

            $scannedCount = $this->currentPresensi->count();
            $this->belumPresensi = $totalSiswa - $scannedCount;
        }

        $this->historySessions = PresensiSession::with(['kelas', 'presensis' => function ($q) {
            $q->selectRaw('status, count(*) as count')
                ->groupBy('status');
        }])
            ->where('is_active', false)
            ->latest('started_at')
            ->limit(10)
            ->get();
    }

    public function getQrDataProperty()
    {
        return $this->qrData;
    }

    public function render()
    {
        return view('livewire.admin.adm-presensi');
    }
}