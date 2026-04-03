<?php

namespace App\Livewire\Admin;

use App\Models\Kelas;
use App\Models\Presensi;
use App\Models\Siswa;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class AdminDashboard extends Component
{
    public $totalSiswa;

    public $totalKelas;

    public $hariIniHadir;

    public $terlambat;

    public $totalPresensiHarianHariIni;

    public $totalPresensiMapelHariIni;

    public $belumPresensiHarian;

    public $presensiHariIni = [];

    public $chartData = [];

    public function mount(): void
    {
        if (! Auth::user()?->isAdmin()) {
            abort(403);
        }
        $this->updateStats();
    }

    public function updateStats(): void
    {
        $this->totalSiswa = Siswa::count();
        $this->totalKelas = Kelas::count();
        $harianToday = Presensi::today()
            ->where('tipe_sesi', 'harian')
            ->whereIn('status', ['hadir', 'terlambat']);
        $this->hariIniHadir = (clone $harianToday)->where('status', 'hadir')->count();
        $this->terlambat = (clone $harianToday)->where('status', 'terlambat')->count();
        $this->totalPresensiHarianHariIni = (clone $harianToday)->count();
        $this->totalPresensiMapelHariIni = Presensi::today()
            ->where('tipe_sesi', 'mapel')
            ->whereIn('status', ['hadir', 'terlambat'])
            ->count();
        $this->belumPresensiHarian = $this->totalSiswa - (clone $harianToday)->distinct('siswa_id')->count('siswa_id');
        $this->presensiHariIni = Presensi::today()
            ->where('tipe_sesi', 'harian')
            ->with('siswa.kelas')
            ->orderBy('waktu', 'desc')
            ->get();

        $this->chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $this->chartData[] = [
                'hari' => $date->format('D'),
                'hadir' => Presensi::whereDate('tanggal', $date)
                    ->where('tipe_sesi', 'harian')
                    ->where('status', 'hadir')
                    ->count(),
            ];
        }
    }

    public function render()
    {
        return view('livewire.admin.admin-dashboard');
    }
}
