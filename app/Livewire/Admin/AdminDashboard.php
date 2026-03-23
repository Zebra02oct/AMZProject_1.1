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
    public $totalPresensiHariIni;
    public $belumPresensi;
    public $presensiHariIni = [];
    public $chartData = [];

    public function mount(): void
    {
        if (!Auth::user()?->isAdmin()) {
            abort(403);
        }
        $this->updateStats();
    }

    public function updateStats(): void
    {
        $this->totalSiswa = Siswa::count();
        $this->totalKelas = Kelas::count();
        $totalPresensiToday = Presensi::today()->count();
        $this->hariIniHadir = Presensi::today()->where('status', 'hadir')->count();
        $this->terlambat = Presensi::today()->where('status', 'terlambat')->count();
        $this->totalPresensiHariIni = $totalPresensiToday;
        $this->belumPresensi = $this->totalSiswa - $totalPresensiToday;
        $this->presensiHariIni = Presensi::today()->with('siswa.kelas')->orderBy('waktu', 'desc')->get();

        $this->chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $this->chartData[] = [
                'hari' => $date->format('D'),
                'hadir' => Presensi::whereDate('tanggal', $date)->where('status', 'hadir')->count(),
            ];
        }
    }

    public function render()
    {
        return view('livewire.admin.admin-dashboard');
    }
}