<?php

namespace App\Exports;

use App\Models\Presensi;
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
        $query = Presensi::with(['siswa', 'session.kelas'])
            ->whereHas('session', fn($q) => $q->where('guru_id', Auth::id())->where('is_active', false));

        extract($this->filters);

        if (isset($kelas_id) && $kelas_id) {
            $query->whereHas('siswa', fn($q) => $q->where('kelas_id', $kelas_id));
        }

        if (isset($date_start) && $date_start) {
            $query->whereDate('tanggal', '>=', $date_start);
        }

        if (isset($date_end) && $date_end) {
            $query->whereDate('tanggal', '<=', $date_end);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Nama Siswa',
            'Kelas',
            'Tanggal',
            'Waktu Scan',
            'Status',
            'Sesi Mulai'
        ];
    }

    public function map($presensi): array
    {
        return [
            $presensi->siswa->name,
            $presensi->session->kelas->name,
            $presensi->tanggal->format('d/m/Y'),
            $presensi->waktu_scan?->format('H:i'),
            ucfirst($presensi->status),
            $presensi->session->started_at->format('d/m/Y H:i')
        ];
    }
}
