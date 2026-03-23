<?php

namespace App\Livewire\Admin;

use App\Models\Kelas;
use App\Models\Presensi;
use App\Models\Siswa;
use Livewire\Component;
use Livewire\WithPagination;

class AdmLaporan extends Component
{
    use WithPagination;

    public $dateFrom;
    public $dateTo;
    public $kelasId;
    public $filtersApplied = false;

    public $totalSiswa = 0;
    public $hadirCount = 0;
    public $telatCount = 0;
    public $absenCount = 0;

    public function mount()
    {
        $this->dateFrom = today()->format('Y-m-d');
        $this->dateTo = today()->format('Y-m-d');
        $this->kelasId = '';
        $this->applyFilters();
    }

    public function applyFilters()
    {
        $this->resetPage();

        // Get filtered presensi for counts
        $presensiQuery = Presensi::dateRange($this->dateFrom, $this->dateTo)
            ->kelasFilter($this->kelasId);

        $this->hadirCount = $presensiQuery->hadir()->count();
        $this->telatCount = $presensiQuery->whereIn('status', ['terlambat', 'terlambat'])->count();

        $presentCount = $this->hadirCount + $this->telatCount;

        // Total siswa in filter
        $siswaQuery = Siswa::query();
        if ($this->kelasId) {
            $siswaQuery->where('kelas_id', $this->kelasId);
        }
        $this->totalSiswa = $siswaQuery->count();

        $this->absenCount = $this->totalSiswa - $presentCount;

        $this->filtersApplied = true;
    }

    public function clearFilters()
    {
        $this->dateFrom = today()->format('Y-m-d');
        $this->dateTo = today()->format('Y-m-d');
        $this->kelasId = '';
        $this->applyFilters();
    }

    public function getPresensiProperty()
    {
        return Presensi::with(['siswa.kelas'])
            ->dateRange($this->dateFrom, $this->dateTo)
            ->kelasFilter($this->kelasId)
            ->orderBy('tanggal', 'desc')
            ->orderBy('waktu', 'asc')
            ->paginate(10);
    }

    public function getKelasListProperty()
    {
        return Kelas::all();
    }

    public function render()
    {
        return view('livewire.admin.adm-laporan', [
            'kelasList' => $this->kelasList,
            'presensi' => $this->presensi,
        ]);
    }
}