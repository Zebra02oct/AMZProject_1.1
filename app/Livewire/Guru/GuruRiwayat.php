<?php

namespace App\Livewire\Guru;

use App\Exports\PresensiSummaryExport;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Presensi;
use App\Models\PresensiSession;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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
        'tipe_sesi' => '',
        'kelas_id' => '',
        'mapel_id' => '',
        'date' => '',
        'date_start' => '',
        'date_end' => '',
        'quick_range' => '',
    ];

    public $presensiList = [];

    public $rekapData = [];

    public $mapelList = [];

    public $canAccessHarian = false;

    public function mount()
    {
        $this->canAccessHarian = Kelas::where('wali_kelas_id', Auth::id())->exists();

        if (! $this->canAccessHarian) {
            $this->filters['tipe_sesi'] = 'mapel';
        }

        $this->normalizeFilters();

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
        $this->normalizeFilters();
        $this->resetPage();
        $this->selectedSession = null;
        $this->presensiList = [];
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

    public function resetFilters()
    {
        $this->filters = [
            'tipe_sesi' => $this->canAccessHarian ? '' : 'mapel',
            'kelas_id' => '',
            'mapel_id' => '',
            'date' => '',
            'date_start' => '',
            'date_end' => '',
            'quick_range' => '',
        ];

        $this->normalizeFilters();
        $this->selectedSession = null;
        $this->presensiList = [];
        $this->rekapData = [];
        $this->resetPage();

        $this->loadRiwayat();

        if ($this->activeTab === 'rekap') {
            $this->loadRekap();
        }
    }

    public function showDetail($sessionId)
    {
        $query = PresensiSession::with(['kelas', 'mapel', 'presensis.siswa'])
            ->where('guru_id', Auth::id());

        if (! $this->canAccessHarian) {
            $query->where('tipe_sesi', 'mapel');
        }

        $this->selectedSession = $query->findOrFail($sessionId);

        $this->presensiList = Presensi::with('siswa')
            ->where('session_id', $sessionId)
            ->orderByRaw("FIELD(status, 'hadir', 'terlambat', 'tidak_hadir')")
            ->orderBy('siswa_id')
            ->orderBy('waktu_scan', 'desc')
            ->get();
    }

    public function updateKeterangan(int $presensiId, string $keterangan): void
    {
        if (! in_array($keterangan, ['tanpa_keterangan', 'sakit', 'izin'], true)) {
            return;
        }

        $presensi = Presensi::whereKey($presensiId)
            ->whereHas('session', function ($query) {
                $query->where('guru_id', Auth::id());
            })
            ->first();

        if (! $presensi || $presensi->status !== 'tidak_hadir') {
            return;
        }

        $presensi->update(['keterangan' => $keterangan]);

        if ($this->selectedSession && (int) $this->selectedSession->id === (int) $presensi->session_id) {
            $this->presensiList = Presensi::with('siswa')
                ->where('session_id', $presensi->session_id)
                ->orderByRaw("FIELD(status, 'hadir', 'terlambat', 'tidak_hadir')")
                ->orderBy('siswa_id')
                ->orderBy('waktu_scan', 'desc')
                ->get();
        }
    }

    public function closeDetail(): void
    {
        $this->selectedSession = null;
        $this->presensiList = [];
    }

    public function loadRekap()
    {
        $this->normalizeFilters();

        $sessionQuery = PresensiSession::query()
            ->where('guru_id', Auth::id())
            ->where('is_active', false)
            ->with(['kelas', 'mapel']);

        $this->applySessionFilters($sessionQuery);

        if ($this->filters['date_start']) {
            $sessionQuery->whereDate('started_at', '>=', $this->filters['date_start']);
        }

        if ($this->filters['date_end']) {
            $sessionQuery->whereDate('started_at', '<=', $this->filters['date_end']);
        }

        $sessions = $sessionQuery->get();
        $sessionIds = $sessions->pluck('id');

        if ($sessionIds->isEmpty()) {
            $this->rekapData = [];

            return;
        }

        $studentQuery = Siswa::with('kelas')
            ->whereHas('kelas', function ($query) use ($sessions) {
                $query->whereIn('id', $sessions->pluck('kelas_id')->unique());
            });

        if ($this->filters['kelas_id']) {
            $studentQuery->where('kelas_id', $this->filters['kelas_id']);
        }

        $students = $studentQuery->orderBy('name')->get();

        $query = Presensi::with('siswa.kelas')
            ->whereIn('session_id', $sessionIds)
            ->whereHas('siswa');

        $data = $query->get()->groupBy('siswa_id');

        $this->rekapData = [];
        foreach ($students as $student) {
            $presensi = $data->get($student->id, collect());
            $hadir = $presensi->where('status', 'hadir')->count();
            $terlambat = $presensi->where('status', 'terlambat')->count();
            $tidakHadir = $presensi->where('status', 'tidak_hadir')->count();
            $sakit = $presensi->where('status', 'tidak_hadir')->where('keterangan', 'sakit')->count();
            $izin = $presensi->where('status', 'tidak_hadir')->where('keterangan', 'izin')->count();
            $tanpaKeterangan = $presensi->where('status', 'tidak_hadir')->where('keterangan', 'tanpa_keterangan')->count();
            $totalSessions = $hadir + $terlambat + $tidakHadir;

            $this->rekapData[] = [
                'siswa' => $student,
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'tidak_hadir' => $tidakHadir,
                'sakit' => $sakit,
                'izin' => $izin,
                'tanpa_keterangan' => $tanpaKeterangan,
                'total_sesi' => $totalSessions,
            ];
        }
    }

    public function exportExcel()
    {
        $this->normalizeFilters();

        if (! $this->canExportExcel()) {
            $this->dispatch('export-filter-required', message: 'Harus pilih rentang cepat, tipe sesi, dan kelas (dan mapel jika tipe mapel).');

            return;
        }

        // Use the summary export (No | Nama | Minggu 1-3 | Total)
        return Excel::download(new PresensiSummaryExport($this->filters), 'rekap-presensi-summary-'.now()->format('Y-m-d').'.xlsx');
    }

    public function exportPdf()
    {
        // TODO: PDF export using DomPDF or similar
        return response()->json(['message' => 'PDF export coming soon']);
    }

    public function render()
    {
        $this->normalizeFilters();

        $kelasList = Kelas::whereIn('id', $this->getAllowedKelasIds())
            ->orderBy('name')
            ->pluck('name', 'id');

        $query = PresensiSession::with(['kelas', 'mapel', 'presensis.siswa'])
            ->where('guru_id', Auth::id())
            ->where('is_active', false);

        $this->applySessionFilters($query);

        if ($this->filters['date']) {
            $query->whereDate('started_at', $this->filters['date']);
        }

        $sessions = $query->latest('started_at')->paginate(10);

        return view('livewire.guru.guru-riwayat', [
            'kelasList' => $kelasList,
            'sessions' => $sessions,
            'showMapelFilter' => $this->filters['tipe_sesi'] !== 'harian',
            'canExportExcel' => $this->canExportExcel(),
        ]);
    }

    private function canExportExcel(): bool
    {
        // Allow export only when a quick range is selected and required filters are chosen
        if (empty($this->filters['quick_range'])) {
            return false;
        }

        if (empty($this->filters['tipe_sesi']) || empty($this->filters['kelas_id'])) {
            return false;
        }

        if ($this->filters['tipe_sesi'] === 'mapel' && empty($this->filters['mapel_id'])) {
            return false;
        }

        return true;
    }

    private function applySessionFilters($query): void
    {
        if (! $this->canAccessHarian) {
            $query->where('tipe_sesi', 'mapel');
        } elseif ($this->filters['tipe_sesi']) {
            $query->where('tipe_sesi', $this->filters['tipe_sesi']);
        }

        if ($this->filters['kelas_id']) {
            $query->where('kelas_id', $this->filters['kelas_id']);
        }

        if ($this->filters['mapel_id']) {
            $query->where('mapel_id', $this->filters['mapel_id']);
        }
    }

    private function applyQuickRangeToDates(string $range): void
    {
        $now = Carbon::now();

        switch ($range) {
            case 'today':
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'week':
                $start = $now->copy()->startOfWeek();
                $end = $now->copy()->endOfWeek();
                break;
            case 'month':
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                break;
            case 'semester':
                // approximate semester as last 6 months including current month
                $start = $now->copy()->subMonths(5)->startOfMonth();
                $end = $now->copy()->endOfMonth();
                break;
            default:
                return;
        }

        $this->filters['date_start'] = $start->toDateString();
        $this->filters['date_end'] = $end->toDateString();
    }

    private function normalizeFilters(): void
    {
        if (! $this->canAccessHarian) {
            $this->filters['tipe_sesi'] = 'mapel';
        }

        if ($this->filters['tipe_sesi'] === 'harian') {
            $this->filters['mapel_id'] = '';
        }

        // If quick range is selected, compute date_start/date_end and disable manual range
        if (! empty($this->filters['quick_range'])) {
            $this->applyQuickRangeToDates($this->filters['quick_range']);
        }

        $allowedKelasIds = $this->getAllowedKelasIds();
        if ($this->filters['kelas_id'] && ! $allowedKelasIds->contains((int) $this->filters['kelas_id'])) {
            $this->filters['kelas_id'] = '';
            $this->filters['mapel_id'] = '';
        }

        $this->refreshMapelList();
    }

    private function refreshMapelList(): void
    {
        if ($this->filters['tipe_sesi'] === 'harian') {
            $this->mapelList = collect();

            return;
        }

        $query = $this->getGuruMapelQuery();

        if ($this->filters['kelas_id']) {
            $query->where('kelas_id', $this->filters['kelas_id']);
        }

        $this->mapelList = $query->orderBy('nama_mapel')->get();

        if ($this->filters['mapel_id'] && ! collect($this->mapelList)->pluck('id')->contains((int) $this->filters['mapel_id'])) {
            $this->filters['mapel_id'] = '';
        }
    }

    private function getAllowedKelasIds()
    {
        $waliKelasIds = Kelas::where('wali_kelas_id', Auth::id())->pluck('id');
        $mapelKelasIds = $this->getGuruMapelQuery()->pluck('kelas_id')->filter();

        if (! $this->canAccessHarian || $this->filters['tipe_sesi'] === 'mapel') {
            return $mapelKelasIds->unique()->values();
        }

        if ($this->filters['tipe_sesi'] === 'harian') {
            return $waliKelasIds->unique()->values();
        }

        return $waliKelasIds->merge($mapelKelasIds)->unique()->values();
    }

    private function getGuruMapelQuery()
    {
        return Mapel::query()->whereHas('gurus', function ($query) {
            $query->where('guru_id', Auth::id());
        });
    }
}
