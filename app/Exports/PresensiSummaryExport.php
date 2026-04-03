<?php

namespace App\Exports;

use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Presensi;
use App\Models\PresensiSession;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PresensiSummaryExport implements FromCollection, WithHeadings
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $guruId = Auth::id();
        $canAccessHarian = Kelas::where('wali_kelas_id', $guruId)->exists();

        $sessionQuery = PresensiSession::query()
            ->where('guru_id', $guruId)
            ->where('is_active', false);

        $filters = $this->filters;

        $tipeSesi = $filters['tipe_sesi'] ?? '';
        if (! $canAccessHarian) {
            $tipeSesi = 'mapel';
        }

        if ($tipeSesi === 'harian') {
            $filters['mapel_id'] = null;
        }

        if (! empty($tipeSesi)) {
            $sessionQuery->where('tipe_sesi', $tipeSesi);
        }

        if (! empty($filters['kelas_id'])) {
            $sessionQuery->where('kelas_id', $filters['kelas_id']);
        }

        $allowedMapelIds = Mapel::whereHas('gurus', function ($query) use ($guruId) {
            $query->where('guru_id', $guruId);
        })
            ->when(! empty($filters['kelas_id']), function ($query) use ($filters) {
                $query->where('kelas_id', $filters['kelas_id']);
            })
            ->pluck('id');

        if (! empty($filters['mapel_id'])) {
            if (! $allowedMapelIds->contains((int) $filters['mapel_id'])) {
                return collect();
            }

            $sessionQuery->where('mapel_id', $filters['mapel_id']);
        } elseif ($tipeSesi === 'mapel') {
            if ($allowedMapelIds->isEmpty()) {
                return collect();
            }

            $sessionQuery->whereIn('mapel_id', $allowedMapelIds);
        }

        if (! empty($filters['date_start'])) {
            $sessionQuery->whereDate('started_at', '>=', $filters['date_start']);
        }

        if (! empty($filters['date_end'])) {
            $sessionQuery->whereDate('started_at', '<=', $filters['date_end']);
        }

        $sessionIds = $sessionQuery->pluck('id');

        if ($sessionIds->isEmpty()) {
            return collect();
        }

        $sessions = PresensiSession::whereIn('id', $sessionIds)->get();
        $kelasIds = $sessions->pluck('kelas_id')->unique();

        $studentQuery = Siswa::with('kelas')
            ->whereIn('kelas_id', $kelasIds->filter());

        if (! empty($filters['kelas_id'])) {
            $studentQuery->where('kelas_id', $filters['kelas_id']);
        }

        $students = $studentQuery->orderBy('name')->get();

        $presensiAll = Presensi::whereIn('session_id', $sessionIds)
            ->with('session')
            ->get()
            ->groupBy('siswa_id');

        // Determine week ranges (consecutive 7-day blocks) between date_start and date_end
        $start = ! empty($filters['date_start']) ? Carbon::parse($filters['date_start'])->startOfDay() : null;
        $end = ! empty($filters['date_end']) ? Carbon::parse($filters['date_end'])->endOfDay() : null;

        if (! $start || ! $end) {
            $start = $sessions->min('started_at') ? Carbon::parse($sessions->min('started_at'))->startOfDay() : Carbon::now()->startOfDay();
            $end = $sessions->max('started_at') ? Carbon::parse($sessions->max('started_at'))->endOfDay() : Carbon::now()->endOfDay();
        }

        $weekRanges = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $rangeStart = $cursor->copy();
            $rangeEnd = $rangeStart->copy()->addDays(6)->endOfDay();
            if ($rangeEnd->gt($end)) {
                $rangeEnd = $end->copy();
            }

            $weekRanges[] = ['start' => $rangeStart, 'end' => $rangeEnd];

            $cursor = $rangeEnd->copy()->addDay()->startOfDay();
        }

        $rows = [];
        $index = 1;

        foreach ($students as $student) {
            $presByStudent = $presensiAll->get($student->id, collect());

            $row = [$index++, $student->name];
            $totalAbsences = 0;

            foreach ($weekRanges as $week) {
                $s = $week['start'];
                $e = $week['end'];

                $izin = $presByStudent->filter(function ($p) use ($s, $e) {
                    $date = $p->tanggal ? Carbon::parse($p->tanggal) : null;

                    return $date && $date->between($s, $e) && $p->status === 'tidak_hadir' && (($p->keterangan ?? 'tanpa_keterangan') === 'izin');
                })->count();

                $sakit = $presByStudent->filter(function ($p) use ($s, $e) {
                    $date = $p->tanggal ? Carbon::parse($p->tanggal) : null;

                    return $date && $date->between($s, $e) && $p->status === 'tidak_hadir' && (($p->keterangan ?? 'tanpa_keterangan') === 'sakit');
                })->count();

                $tanpa = $presByStudent->filter(function ($p) use ($s, $e) {
                    $date = $p->tanggal ? Carbon::parse($p->tanggal) : null;

                    return $date && $date->between($s, $e) && $p->status === 'tidak_hadir' && (($p->keterangan ?? 'tanpa_keterangan') === 'tanpa_keterangan');
                })->count();

                $row[] = $izin;
                $row[] = $sakit;
                $row[] = $tanpa;

                $totalAbsences += ($izin + $sakit + $tanpa);
            }

            $row[] = $totalAbsences;
            $rows[] = $row;
        }

        return new Collection($rows);
    }

    public function headings(): array
    {
        $filters = $this->filters ?? [];

        $start = ! empty($filters['date_start']) ? Carbon::parse($filters['date_start'])->startOfDay() : null;
        $end = ! empty($filters['date_end']) ? Carbon::parse($filters['date_end'])->endOfDay() : null;

        if (! $start || ! $end) {
            $start = Carbon::now()->startOfDay();
            $end = Carbon::now()->endOfDay();
        }

        $weekRanges = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $rangeStart = $cursor->copy();
            $rangeEnd = $rangeStart->copy()->addDays(6)->endOfDay();
            if ($rangeEnd->gt($end)) {
                $rangeEnd = $end->copy();
            }

            $weekRanges[] = ['start' => $rangeStart, 'end' => $rangeEnd];

            $cursor = $rangeEnd->copy()->addDay()->startOfDay();
        }

        $head = ['No', 'Nama'];
        foreach ($weekRanges as $i => $w) {
            $idx = $i + 1;
            $head[] = "Minggu {$idx} I";
            $head[] = "Minggu {$idx} S";
            $head[] = "Minggu {$idx} A";
        }

        $head[] = 'Total';

        return $head;
    }
}
