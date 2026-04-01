<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-[#7a4f16] dark:text-[#ffd889]">Dashboard Guru</h1>
            <p class="text-lg text-[#8b6a3c] dark:text-[#e5c58d] mt-1">Kelola presensi kelas dan mata pelajaran Anda</p>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
        <div
            class="bg-white/95 dark:bg-[#3a2a13] p-8 rounded-2xl shadow-lg border border-[#ecd6aa] dark:border-[#8d662b] hover:shadow-xl transition-all">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-[#8f4f11] to-[#b97820] rounded-xl">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-[#7a4f16] dark:text-[#ffd889]">Mulai Presensi</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Pilih kelas dan mulai sesi baru</p>
                </div>
            </div>
            <div class="mt-6">
                <a href="{{ route('guru.presensi') }}"
                    class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-[#8f4f11] to-[#b97820] text-[#fff8ec] font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
                    Mulai Sekarang
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>

        <div
            class="bg-white/95 dark:bg-[#3a2a13] p-8 rounded-2xl shadow-lg border border-[#ecd6aa] dark:border-[#8d662b] hover:shadow-xl transition-all">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-[#5f7a18] to-[#89a827] rounded-xl">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422A12.083 12.083 0 0112 20.055a12.083 12.083 0 01-6.16-9.477L12 14z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-[#7a4f16] dark:text-[#ffd889]">Presensi Mapel</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Buka sesi presensi per mata pelajaran</p>
                </div>
            </div>
            <div class="mt-6">
                <a href="{{ route('guru.presensi-mapel') }}"
                    class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-[#5f7a18] to-[#89a827] text-[#fff8ec] font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
                    Buka Presensi Mapel
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div
            class="bg-white/95 dark:bg-[#3a2a13] p-8 rounded-2xl shadow-lg border border-[#ecd6aa] dark:border-[#8d662b]">
            <h3 class="text-lg font-semibold text-[#7a4f16] dark:text-[#ffd889] mb-6">Kelas Wali Anda</h3>
            @if ($waliKelas)
                <div class="group p-5 bg-gradient-to-br from-[#f8e9c8] to-[#f3dfb7] dark:from-[#5a401a] dark:to-[#4a3618] rounded-xl border border-[#ecd6aa] dark:border-[#8d662b] hover:shadow-lg transition-all cursor-pointer"
                    onclick="window.location='{{ route('guru.presensi') }}'">
                    <div
                        class="text-3xl font-bold bg-gradient-to-r from-[#8f4f11] to-[#b97820] bg-clip-text text-transparent mb-2">
                        {{ $waliKelas->name }}
                    </div>
                    <div class="text-xs text-[#8f4f11] dark:text-[#f0c66f] font-medium">
                        {{ $waliKelas->siswa->count() }} siswa
                    </div>
                </div>
            @else
                <div class="text-center py-12 text-[#8b6a3c] dark:text-[#e5c58d]">
                    Belum ada kelas wali
                </div>
            @endif
        </div>

        <div
            class="bg-white/95 dark:bg-[#3a2a13] p-8 rounded-2xl shadow-lg border border-[#ecd6aa] dark:border-[#8d662b]">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-[#7a4f16] dark:text-[#ffd889]">Mata Pelajaran yang Diampu</h3>
                <span
                    class="text-xs font-semibold px-3 py-1 rounded-full bg-[#f8e9c8] text-[#8f4f11] dark:bg-[#5a401a] dark:text-[#f0c66f]">
                    {{ $mapelList->count() }} mapel
                </span>
            </div>
            <div class="space-y-3 max-h-[420px] overflow-y-auto pr-1">
                @forelse($mapelList as $mapel)
                    <div
                        class="p-4 bg-gradient-to-br from-[#f8e9c8] to-[#f3dfb7] dark:from-[#5a401a] dark:to-[#4a3618] rounded-xl border border-[#ecd6aa] dark:border-[#8d662b]">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="font-semibold text-[#7a4f16] dark:text-[#ffd889]">{{ $mapel->nama_mapel }}
                                </div>
                                <div class="text-xs text-[#8f4f11] dark:text-[#f0c66f] mt-1">
                                    {{ $mapel->kelas?->name ?? 'Tanpa kelas' }}
                                </div>
                            </div>
                            <a href="{{ route('guru.presensi-mapel') }}"
                                class="shrink-0 px-3 py-1.5 text-xs font-semibold rounded-lg bg-[#8f4f11] text-white hover:bg-[#7a420f] transition-all">
                                Buka
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-[#8b6a3c] dark:text-[#e5c58d]">
                        Belum ada mata pelajaran
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent Sessions --}}
    <div
        class="bg-white/95 dark:bg-[#3a2a13] rounded-2xl shadow-lg border border-[#ecd6aa] dark:border-[#8d662b] overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-600">
            <h3 class="text-xl font-semibold text-[#7a4f16] dark:text-[#ffd889]">Sesi Terbaru</h3>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($recentSessions as $session)
                <div class="p-6 hover:bg-[#fff3dc] dark:hover:bg-[#4a3618] transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-[#7a4f16] dark:text-[#ffd889]">
                                @if ($session->tipe_sesi === 'mapel' && $session->mapel)
                                    {{ $session->mapel->nama_mapel }}
                                @else
                                    {{ $session->kelas->name }}
                                @endif
                            </div>
                            <div class="text-sm text-[#8b6a3c] dark:text-[#e5c58d]">
                                {{ $session->started_at->format('d/m/Y H:i') }}
                                <span
                                    class="ml-2 px-2 py-1 text-xs font-medium rounded-full {{ $session->tipe_sesi === 'mapel' ? 'bg-[#e7f3d1] text-[#5f7a18] dark:bg-[#5f7a18] dark:text-[#e7f3d1]' : 'bg-[#f8e9c8] text-[#8f4f11] dark:bg-[#5a401a] dark:text-[#f0c66f]' }}">
                                    {{ $session->tipe_sesi === 'mapel' ? 'Mapel' : 'Harian' }}
                                </span>
                                @if ($session->is_active)
                                    <span
                                        class="ml-2 px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">Aktif</span>
                                @else
                                    <span
                                        class="ml-2 px-2 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded-full">Selesai</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-[#7a4f16] dark:text-[#ffd889]">
                                {{ $session->presensis()->count() }}/{{ $session->kelas->siswa->count() }}
                            </div>
                            <div class="text-xs text-[#8b6a3c] dark:text-[#e5c58d]">kehadiran</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-[#8b6a3c] dark:text-[#e5c58d]">
                    Belum ada sesi presensi
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    // Smooth transitions
    document.addEventListener('alpine:init', () => {
        Alpine.data('guruDashboard', () => ({
            init() {
                // Add loading states or animations
            }
        }))
    })
</script>
