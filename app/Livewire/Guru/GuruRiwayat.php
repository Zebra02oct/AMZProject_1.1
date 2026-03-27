<?php

namespace App\Livewire\Guru;

use App\Exports\PresensiExport;
use App\Models\Kelas;
use App\Models\Presensi;
use App\Models\PresensiSession;
use App\Models\Siswa;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class GuruRiwayat extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $activeTab = 'riwayat';
    public $selectedSession = null;
    public $filters = [
        'kelas_id' => '',
        'date' => '',
        'date_start' => '',
        'date_end' => ''
    ];

    public $presensiList = [];
    public $rekapData = [];

    public function mount()
    {
        $this->loadRiwayat();
    }

    public function updatedActiveTab()
    {
        if ($this->activeTab === 'rekap') {
            $this->loadRekap();
        }
    }

    public function updatedFilters()
    {
        $this->resetPage();
        if ($this->activeTab === 'riwayat') {
            $this->loadRiwayat();
        } else {
            $this->loadRekap();
        }
    }

    public function loadRiwayat()
    {
        // Nothing to assign to public property (Livewire tidak mendukung property paginator)
        // queries akan dieksekusi di render() untuk mendukung paging dan filter
    }

    public function showDetail($sessionId)
    {
        $this->selectedSession = PresensiSession::with(['kelas', 'presensis.siswa'])
            ->where('guru_id', Auth::id())
            ->findOrFail($sessionId);

        $this->presensiList = Presensi::with('siswa')
            ->where('session_id', $sessionId)
            ->orderBy('waktu_scan', 'desc')
            ->get();
    }

    public function loadRekap()
    {
        $query = Presensi::with('siswa')
            ->whereHas('session', function ($q) {
                $q->where('guru_id', Auth::id())
                    ->where('is_active', false);
            });

        if ($this->filters['kelas_id']) {
            $query->whereHas('siswa', fn($q) => $q->where('kelas_id', $this->filters['kelas_id']));
        }

        if ($this->filters['date_start']) {
            $query->whereDate('tanggal', '>=', $this->filters['date_start']);
        }

        if ($this->filters['date_end']) {
            $query->whereDate('tanggal', '<=', $this->filters['date_end']);
        }

        $data = $query->get()->groupBy('siswa_id');

        $this->rekapData = [];
        foreach ($data as $siswaId => $presensi) {
            $hadir = $presensi->where('status', 'hadir')->count();
            $terlambat = $presensi->where('status', 'terlambat')->count();
            $totalSessions = PresensiSession::where('guru_id', Auth::id())
                ->where('is_active', false)
                ->whereBetween('started_at', [$this->filters['date_start'] ?? '2000-01-01', $this->filters['date_end'] ?? now()])
                ->count();
            $tidakHadir = $totalSessions - $hadir - $terlambat;

            $this->rekapData[] = [
                'siswa' => $presensi->first()->siswa,
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'tidak_hadir' => $tidakHadir
            ];
        }
    }

    public function exportExcel()
    {
        return Excel::download(new PresensiExport($this->filters), 'rekap-presensi-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportPdf()
    {
        // TODO: PDF export using DomPDF or similar
        return response()->json(['message' => 'PDF export coming soon']);
    }

    public function render()
    {
        $kelasList = Kelas::whereHas('siswa')->pluck('name', 'id');

        $query = PresensiSession::with(['kelas', 'presensis'])
            ->where('guru_id', Auth::id())
            ->where('is_active', false);

        if ($this->filters['kelas_id']) {
            $query->where('kelas_id', $this->filters['kelas_id']);
        }

        if ($this->filters['date']) {
            $query->whereDate('started_at', $this->filters['date']);
        }

        $sessions = $query->latest('started_at')->paginate(10);

        return view('livewire.guru.guru-riwayat', [
            'kelasList' => $kelasList,
            'sessions' => $sessions,
        ]);
    }
}
