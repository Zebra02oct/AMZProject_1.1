<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Dashboard Guru</h1>
            <p class="text-lg text-gray-600 dark:text-gray-300 mt-1">Kelola presensi kelas Anda</p>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
            class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-all">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Mulai Presensi</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Pilih kelas dan mulai sesi baru</p>
                </div>
            </div>
            <div class="mt-6">
                <a href="{{ route('guru.presensi') }}"
                    class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
                    Mulai Sekarang
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>

        <div
            class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 col-span-1 md:col-span-2">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Kelas Anda</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @forelse($kelasList as $kelas)
                    <div class="group p-4 bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/30 rounded-xl border border-indigo-200/50 hover:shadow-lg transition-all cursor-pointer"
                        onclick="window.location='{{ route('guru.presensi.create', $kelas->id) }}'">
                        <div
                            class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent mb-2">
                            {{ $kelas->name }}
                        </div>
                        <div class="text-xs text-indigo-700 dark:text-indigo-300 font-medium">
                            {{ $kelas->siswa->count() }} siswa
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400">
                        Belum ada kelas
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent Sessions --}}
    <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-600">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Sesi Terbaru</h3>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($recentSessions as $session)
                <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-gray-900 dark:text-white">{{ $session->kelas->name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $session->started_at->format('d/m/Y H:i') }}
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
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $session->presensis()->count() }}/{{ $session->kelas->siswa->count() }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">kehadiran</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-gray-500 dark:text-gray-400">
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
