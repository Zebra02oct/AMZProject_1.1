<?php

namespace App\Livewire\Guru;

use App\Models\Kelas;
use App\Models\PresensiSession;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GuruDashboard extends Component
{
    public function render()
    {
        $kelasList = Kelas::whereHas('siswa')->get(); // TODO: Filter by guru-taught classes if pivot exists
        $recentSessions = PresensiSession::with('kelas')
            ->where('guru_id', Auth::id())
            ->latest('started_at')
            ->limit(5)
            ->get();

        return view('livewire.guru.guru-dashboard', [
            'kelasList' => $kelasList,
            'recentSessions' => $recentSessions
        ]);
    }
}
