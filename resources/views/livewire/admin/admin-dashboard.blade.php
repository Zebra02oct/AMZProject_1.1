<div wire:poll.5s>
    <div
        class="p-6 lg:p-8 space-y-8 rounded-3xl bg-gradient-to-br from-[#fffaf0] via-[#fff6e6] to-[#f4ead4] border border-[#efd9ac] shadow-[0_22px_60px_rgba(90,55,7,0.18)] dark:from-[#2f210f] dark:via-[#3a2a13] dark:to-[#4a3618] dark:border-[#8d662b]">
        <!-- Header & Quick Actions -->
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4">
            <div>
                <h1 class="text-4xl font-bold text-[#7a4f16] dark:text-[#ffd889] mb-1">Dashboard Admin</h1>
                <p class="text-xl text-[#8b6a3c] dark:text-[#e5c58d]">Ringkasan aktivitas presensi hari ini</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.siswa') }}"
                    class="px-6 py-3 rounded-xl font-semibold transition-all duration-200 bg-[#8f4f11] text-[#fff8ec] hover:bg-[#7b430e] hover:shadow-[0_10px_24px_rgba(90,55,7,0.35)] wire:navigate">
                    ➕ Tambah Siswa
                </a>
                <a href="{{ route('admin.kelas') }}"
                    class="px-6 py-3 rounded-xl font-semibold transition-all duration-200 bg-[#b97820] text-[#fff8ec] hover:bg-[#a3691b] hover:shadow-[0_10px_24px_rgba(90,55,7,0.35)] wire:navigate">
                    ➕ Tambah Kelas
                </a>
                <a href="{{ route('admin.laporan') }}"
                    class="px-6 py-3 rounded-xl font-semibold transition-all duration-200 bg-[#d89932] text-[#4f3110] hover:bg-[#c98b27] hover:shadow-[0_10px_24px_rgba(90,55,7,0.28)] wire:navigate">
                    📊 Lihat Laporan
                </a>
            </div>
        </div>

        <!-- Statistik Utama -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
            <!-- Total Siswa -->
            <div
                class="rounded-2xl bg-white/90 p-8 shadow-lg hover:shadow-2xl transition-all border border-[#ecd6aa] dark:bg-[#3a2a13] dark:border-[#8d662b]">
                <div class="flex items-center">
                    <div class="p-4 rounded-2xl bg-[#f7e5c0] dark:bg-[#5a401a]">
                        <svg class="w-8 h-8 text-[#8f4f11] dark:text-[#ffd889]" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-6">
                        <p class="text-sm font-medium text-[#8b6a3c] dark:text-[#e5c58d] mb-2">Total Siswa</p>
                        <p class="text-4xl font-bold text-[#7a4f16] dark:text-[#ffd889]">{{ $totalSiswa }}</p>
                    </div>
                </div>
            </div>

            <!-- Total Kelas -->
            <div
                class="rounded-2xl bg-white/90 p-8 shadow-lg hover:shadow-2xl transition-all border border-[#ecd6aa] dark:bg-[#3a2a13] dark:border-[#8d662b]">
                <div class="flex items-center">
                    <div class="p-4 rounded-2xl bg-[#f7e5c0] dark:bg-[#5a401a]">
                        <svg class="w-8 h-8 text-[#b97820] dark:text-[#f0c66f]" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-6 0h1m-1 4h1m-1 4h1M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-6">
                        <p class="text-sm font-medium text-[#8b6a3c] dark:text-[#e5c58d] mb-2">Total Kelas</p>
                        <p class="text-4xl font-bold text-[#7a4f16] dark:text-[#ffd889]">{{ $totalKelas }}</p>
                    </div>
                </div>
            </div>

            <!-- Hadir Hari Ini -->
            <div
                class="rounded-2xl bg-white/90 p-8 shadow-lg hover:shadow-2xl transition-all border border-[#ecd6aa] dark:bg-[#3a2a13] dark:border-[#8d662b]">
                <div class="flex items-center">
                    <div class="p-4 rounded-2xl bg-[#f7e5c0] dark:bg-[#5a401a]">
                        <svg class="w-8 h-8 text-[#8f4f11] dark:text-[#ffd889]" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-6">
                        <p class="text-sm font-medium text-[#8b6a3c] dark:text-[#e5c58d] mb-2">Hadir Harian</p>
                        <p class="text-4xl font-bold text-[#7a4f16] dark:text-[#ffd889]">{{ $hariIniHadir }}</p>
                    </div>
                </div>
            </div>

            <!-- Terlambat Hari Ini -->
            <div
                class="rounded-2xl bg-white/90 p-8 shadow-lg hover:shadow-2xl transition-all border border-[#ecd6aa] dark:bg-[#3a2a13] dark:border-[#8d662b]">
                <div class="flex items-center">
                    <div class="p-4 rounded-2xl bg-[#f7e5c0] dark:bg-[#5a401a]">
                        <svg class="w-8 h-8 text-[#d89932] dark:text-[#f0c66f]" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-6">
                        <p class="text-sm font-medium text-[#8b6a3c] dark:text-[#e5c58d] mb-2">Terlambat Harian</p>
                        <p class="text-4xl font-bold text-[#7a4f16] dark:text-[#ffd889]">{{ $terlambat }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ringkasan Tambahan -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
            <div
                class="rounded-2xl bg-white/90 p-8 shadow-lg border border-[#ecd6aa] dark:bg-[#3a2a13] dark:border-[#8d662b]">
                <h3 class="text-xl font-semibold mb-3 text-[#7a4f16] dark:text-[#ffd889]">Total Presensi Harian</h3>
                <p class="text-4xl font-bold text-[#8f4f11] dark:text-[#f0c66f]">{{ $totalPresensiHarianHariIni }}</p>
            </div>
            <div
                class="rounded-2xl bg-white/90 p-8 shadow-lg border border-[#ecd6aa] dark:bg-[#3a2a13] dark:border-[#8d662b]">
                <h3 class="text-xl font-semibold mb-3 text-[#7a4f16] dark:text-[#ffd889]">Belum Presensi Harian</h3>
                <p class="text-4xl font-bold text-[#b97820] dark:text-[#f0c66f]">{{ $belumPresensiHarian }}</p>
                <p class="text-sm text-[#8b6a3c] dark:text-[#e5c58d] mt-1">Siswa belum scan QR harian</p>
            </div>
            <div
                class="rounded-2xl bg-white/90 p-8 shadow-lg border border-[#ecd6aa] dark:bg-[#3a2a13] dark:border-[#8d662b]">
                <h3 class="text-xl font-semibold mb-3 text-[#7a4f16] dark:text-[#ffd889]">Presensi Mapel</h3>
                <p class="text-4xl font-bold text-[#5f7a18] dark:text-[#c8e56a]">{{ $totalPresensiMapelHariIni }}</p>
                <p class="text-sm text-[#8b6a3c] dark:text-[#e5c58d] mt-1">Total scan sesi mapel hari ini</p>
            </div>
        </div>

        <!-- Tabel Presensi Hari Ini -->
        <div
            class="bg-white/95 rounded-3xl shadow-2xl border border-[#ecd6aa] dark:bg-[#3a2a13] dark:border-[#8d662b] overflow-hidden mb-12">
            <div class="p-8 border-b border-[#edd7ad] dark:border-[#8d662b]">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-3xl font-bold text-[#7a4f16] dark:text-[#ffd889] mb-1">Presensi Harian Hari Ini
                        </h2>
                        <p class="text-lg text-[#8b6a3c] dark:text-[#e5c58d]">Data di bawah hanya sesi harian, auto
                            refresh 5 detik</p>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-zinc-900/50">
                            <th class="px-8 py-5 text-left text-lg font-bold text-[#7a4f16] dark:text-[#ffd889]">Nama
                                Siswa
                            </th>
                            <th class="px-8 py-5 text-left text-lg font-bold text-[#7a4f16] dark:text-[#ffd889]">Kelas
                            </th>
                            <th class="px-8 py-5 text-left text-lg font-bold text-[#7a4f16] dark:text-[#ffd889]">Waktu
                                Scan
                            </th>
                            <th class="px-8 py-5 text-left text-lg font-bold text-[#7a4f16] dark:text-[#ffd889]">Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                        @forelse ($presensiHariIni as $p)
                            <tr class="hover:bg-[#fff3dc] dark:hover:bg-[#4a3618] transition-colors">
                                <td class="px-8 py-5 font-semibold text-[#7a4f16] dark:text-[#ffd889]">
                                    {{ $p->siswa->name }}
                                </td>
                                <td class="px-8 py-5">
                                    <span
                                        class="px-3 py-1 bg-[#f7e5c0] text-[#8f4f11] rounded-full text-sm font-medium dark:bg-[#5a401a] dark:text-[#f0c66f]">{{ $p->siswa->kelas->name }}</span>
                                </td>
                                <td class="px-8 py-5 font-medium text-[#7a4f16] dark:text-[#ffd889]">
                                    {{ $p->waktu->format('H:i') }}</td>
                                <td class="px-8 py-5">
                                    <span
                                        class="px-4 py-2 font-semibold rounded-full text-sm
                                    {{ $p->status === 'hadir' ? 'bg-[#f7e5c0] text-[#8f4f11] dark:bg-[#5a401a] dark:text-[#f0c66f]' : 'bg-[#f3dcc8] text-[#8f4f11] dark:bg-[#694a21] dark:text-[#ffd889]' }}">
                                        {{ ucfirst($p->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-8 py-16 text-center text-[#8b6a3c] dark:text-[#e5c58d]">
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
        <div class="bg-white/95 rounded-3xl shadow-2xl border border-[#ecd6aa] dark:bg-[#3a2a13] dark:border-[#8d662b]">
            <div class="p-8 border-b border-[#edd7ad] dark:border-[#8d662b]">
                <h2 class="text-3xl font-bold text-[#7a4f16] dark:text-[#ffd889]">Statistik Kehadiran Minggu Ini</h2>
            </div>
            <div class="p-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-6">
                @foreach ($chartData as $day)
                    <div class="text-center group">
                        <p class="font-bold text-lg text-[#7a4f16] dark:text-[#ffd889] mb-3">{{ $day['hari'] }}</p>
                        <div
                            class="w-full bg-[#f1ddba] rounded-full h-6 mb-2 dark:bg-[#5a401a] group-hover:bg-[#ecd3a5] transition-colors">
                            <div class="bg-gradient-to-r from-[#b97820] to-[#d89932] h-6 rounded-full shadow-md transition-all"
                                style="width: {{ min(100, $day['hadir'] * 3) }}%;"></div>
                        </div>
                        <p class="text-xl font-bold text-[#8f4f11] dark:text-[#f0c66f]">{{ $day['hadir'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
