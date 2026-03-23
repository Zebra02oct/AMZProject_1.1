<?php

use Livewire\Volt\Component;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Presensi;

new class extends Component {
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
        if (!auth()->user()?->isAdmin()) {
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
}; ?>

<div wire:poll.5s>
    <div class="p-6 lg:p-8 space-y-8">
        <!-- Header & Quick Actions -->
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4">
            <div>
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-1">Dashboard</h1>
                <p class="text-xl text-gray-600 dark:text-gray-400">Ringkasan aktivitas presensi hari ini</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.siswa') }}"
                    class="px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition-all wire:navigate">
                    ➕ Tambah Siswa
                </a>
                <a href="{{ route('admin.kelas') }}"
                    class="px-6 py-3 bg-emerald-600 text-white rounded-xl font-semibold hover:bg-emerald-700 transition-all wire:navigate">
                    ➕ Tambah Kelas
                </a>
                <a href="{{ route('admin.laporan') }}"
                    class="px-6 py-3 bg-purple-600 text-white rounded-xl font-semibold hover:bg-purple-700 transition-all wire:navigate">
                    📊 Lihat Laporan
                </a>
            </div>
        </div>

        <!-- Statistik Utama -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Siswa -->
            <div
                class="rounded-2xl bg-white p-8 shadow-lg hover:shadow-2xl transition-all border border-gray-100 dark:bg-zinc-800 dark:border-zinc-700">
                <div class="flex items-center">
                    <div class="p-4 rounded-2xl bg-blue-100 dark:bg-blue-900/30">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-6">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Total Siswa</p>
                        <p class="text-4xl font-bold text-gray-900 dark:text-white">{{ $totalSiswa }}</p>
                    </div>
                </div>
            </div>

            <!-- Total Kelas -->
            <div
                class="rounded-2xl bg-white p-8 shadow-lg hover:shadow-2xl transition-all border border-gray-100 dark:bg-zinc-800 dark:border-zinc-700">
                <div class="flex items-center">
                    <div class="p-4 rounded-2xl bg-emerald-100 dark:bg-emerald-900/30">
                        <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-6 0h1m-1 4h1m-1 4h1M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-6">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Total Kelas</p>
                        <p class="text-4xl font-bold text-gray-900 dark:text-white">{{ $totalKelas }}</p>
                    </div>
                </div>
            </div>

            <!-- Hadir Hari Ini -->
            <div
                class="rounded-2xl bg-white p-8 shadow-lg hover:shadow-2xl transition-all border border-gray-100 dark:bg-zinc-800 dark:border-zinc-700">
                <div class="flex items-center">
                    <div class="p-4 rounded-2xl bg-green-100 dark:bg-green-900/30">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-6">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Hadir Hari Ini</p>
                        <p class="text-4xl font-bold text-gray-900 dark:text-white">{{ $hariIniHadir }}</p>
                    </div>
                </div>
            </div>

            <!-- Terlambat Hari Ini -->
            <div
                class="rounded-2xl bg-white p-8 shadow-lg hover:shadow-2xl transition-all border border-gray-100 dark:bg-zinc-800 dark:border-zinc-700">
                <div class="flex items-center">
                    <div class="p-4 rounded-2xl bg-orange-100 dark:bg-orange-900/30">
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-6">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Terlambat Hari Ini</p>
                        <p class="text-4xl font-bold text-gray-900 dark:text-white">{{ $terlambat }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ringkasan Tambahan -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
            <div
                class="rounded-2xl bg-white p-8 shadow-lg border border-gray-100 dark:bg-zinc-800 dark:border-zinc-700">
                <h3 class="text-xl font-semibold mb-3">Total Presensi Hari Ini</h3>
                <p class="text-4xl font-bold text-blue-600">{{ $totalPresensiHariIni }}</p>
            </div>
            <div
                class="rounded-2xl bg-white p-8 shadow-lg border border-gray-100 dark:bg-zinc-800 dark:border-zinc-700">
                <h3 class="text-xl font-semibold mb-3">Belum Presensi</h3>
                <p class="text-4xl font-bold text-red-600">{{ $belumPresensi }}</p>
                <p class="text-sm text-gray-500 mt-1">Siswa belum scan QR/hadir</p>
            </div>
        </div>

        <!-- Tabel Presensi Hari Ini -->
        <div
            class="bg-white rounded-3xl shadow-2xl border border-gray-100 dark:bg-zinc-800 dark:border-zinc-700 overflow-hidden mb-12">
            <div class="p-8 border-b border-gray-200 dark:border-zinc-700">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">Presensi Hari Ini</h2>
                        <p class="text-lg text-gray-600 dark:text-gray-400">Terbaru di atas, auto refresh 5 detik</p>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-zinc-900/50">
                            <th class="px-8 py-5 text-left text-lg font-bold text-gray-900 dark:text-white">Nama Siswa
                            </th>
                            <th class="px-8 py-5 text-left text-lg font-bold text-gray-900 dark:text-white">Kelas</th>
                            <th class="px-8 py-5 text-left text-lg font-bold text-gray-900 dark:text-white">Waktu Scan
                            </th>
                            <th class="px-8 py-5 text-left text-lg font-bold text-gray-900 dark:text-white">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                        @forelse ($presensiHariIni as $p)
                            <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors">
                                <td class="px-8 py-5 font-semibold text-gray-900 dark:text-white">{{ $p->siswa->name }}
                                </td>
                                <td class="px-8 py-5">
                                    <span
                                        class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">{{ $p->siswa->kelas->name }}</span>
                                </td>
                                <td class="px-8 py-5 font-medium text-gray-900 dark:text-white">
                                    {{ $p->waktu->format('H:i') }}</td>
                                <td class="px-8 py-5">
                                    <span
                                        class="px-4 py-2 font-semibold rounded-full text-sm
                                    {{ $p->status === 'hadir' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                                        {{ ucfirst($p->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-8 py-16 text-center text-gray-500 dark:text-gray-400">
                                    <div class="text-4xl mb-4">📭</div>
                                    Tidak ada presensi hari ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Grafik Mingguan -->
        <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 dark:bg-zinc-800 dark:border-zinc-700">
            <div class="p-8 border-b border-gray-200 dark:border-zinc-700">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Statistik Kehadiran Minggu Ini</h2>
            </div>
            <div class="p-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-6">
                @foreach ($chartData as $day)
                    <div class="text-center group">
                        <p class="font-bold text-lg text-gray-900 dark:text-white mb-3">{{ $day['hari'] }}</p>
                        <div
                            class="w-full bg-gray-200 rounded-full h-6 mb-2 dark:bg-zinc-600 group-hover:bg-gray-300 transition-colors">
                            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-6 rounded-full shadow-md transition-all"
                                style="width: {{ min(100, $day['hadir'] * 3) }}%;"></div>
                        </div>
                        <p class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ $day['hadir'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
