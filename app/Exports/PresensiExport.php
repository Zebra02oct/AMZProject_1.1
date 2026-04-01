<?php

namespace App\Exports;

use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Presensi;
use App\Models\PresensiSession;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PresensiExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters)
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

        extract($this->filters);

        $tipeSesi = $tipe_sesi ?? '';
        if (!$canAccessHarian) {
            $tipeSesi = 'mapel';
        }

        if ($tipeSesi === 'harian') {
            $mapel_id = null;
        }

        if (!empty($tipeSesi)) {
            $sessionQuery->where('tipe_sesi', $tipeSesi);
        }

        if (isset($kelas_id) && $kelas_id) {
            $sessionQuery->where('kelas_id', $kelas_id);
        }

        $allowedMapelIds = Mapel::whereHas('gurus', function ($query) use ($guruId) {
            $query->where('guru_id', $guruId);
        })
            ->when(isset($kelas_id) && $kelas_id, function ($query) use ($kelas_id) {
                $query->where('kelas_id', $kelas_id);
            })
            ->pluck('id');

        if (isset($mapel_id) && $mapel_id) {
            if (!$allowedMapelIds->contains((int) $mapel_id)) {
                return collect();
            }

            $sessionQuery->where('mapel_id', $mapel_id);
        } elseif ($tipeSesi === 'mapel') {
            if ($allowedMapelIds->isEmpty()) {
                return collect();
            }

            $sessionQuery->whereIn('mapel_id', $allowedMapelIds);
        }

        if (isset($date_start) && $date_start) {
            $sessionQuery->whereDate('started_at', '>=', $date_start);
        }

        if (isset($date_end) && $date_end) {
            $sessionQuery->whereDate('started_at', '<=', $date_end);
        }

        if (isset($date) && $date) {
            $sessionQuery->whereDate('started_at', $date);
        }

        $sessionIds = $sessionQuery->pluck('id');

        if ($sessionIds->isEmpty()) {
            return collect();
        }

        $query = Presensi::with(['siswa', 'session.kelas', 'session.mapel'])
            ->whereIn('session_id', $sessionIds);

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Nama Siswa',
            'Kelas',
            'Tipe Sesi',
            'Mata Pelajaran',
            'Tanggal',
            'Waktu Scan',
            'Status',
            'Keterangan',
            'Sesi Mulai',
            'Sesi Selesai'
        ];
    }

    public function map($presensi): array
    {
        return [
            $presensi->siswa->name,
            $presensi->session->kelas->name,
            ucfirst($presensi->session->tipe_sesi ?? '-'),
            $presensi->session->mapel?->nama_mapel ?? '-',
            $presensi->tanggal->format('d/m/Y'),
            $presensi->waktu_scan?->format('H:i'),
            ucfirst($presensi->status),
            $presensi->status === 'tidak_hadir'
                ? ($presensi->keterangan === 'sakit' ? 'Sakit' : 'Tanpa Keterangan')
                : '-',
            $presensi->session->started_at->format('d/m/Y H:i'),
            $presensi->session->ended_at?->format('d/m/Y H:i') ?? '-'
        ];
    }
}
