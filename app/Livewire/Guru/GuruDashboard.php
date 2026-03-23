<?php

namespace App\Livewire\Guru;

use App\Models\Kelas;
use App\Models\PresensiSession;
use Livewire\Component;

class GuruDashboard extends Component
{
    public function render()
    {
        $kelasList = Kelas::whereHas('siswa')->get();
        $recentSessions = PresensiSession::with('kelas')
            ->where('guru_id', auth()->id())
            ->latest('started_at')
            ->limit(5)
            ->get();

        return view('livewire.guru.guru-dashboard', [
            'kelasList' => $kelasList,
            'recentSessions' => $recentSessions
        ]);
    }
}