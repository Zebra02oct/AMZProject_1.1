<?php

namespace App\Livewire\Guru;

use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\PresensiSession;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GuruDashboard extends Component
{
    public function render()
    {
        $guruId = Auth::id();

        $waliKelas = Kelas::with('siswa')
            ->where('wali_kelas_id', $guruId)
            ->first();

        $mapelList = Mapel::whereHas('gurus', function ($query) use ($guruId) {
            $query->where('guru_id', $guruId);
        })
            ->with('kelas')
            ->orderBy('nama_mapel')
            ->get();

        $recentSessions = PresensiSession::with(['kelas', 'mapel'])
            ->where('guru_id', $guruId)
            ->latest('started_at')
            ->limit(5)
            ->get();

        return view('livewire.guru.guru-dashboard', [
            'waliKelas' => $waliKelas,
            'mapelList' => $mapelList,
            'recentSessions' => $recentSessions,
        ]);
    }
}
