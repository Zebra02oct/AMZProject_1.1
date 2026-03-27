<div>
    <div class="max-w-7xl mx-auto p-6 space-y-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Riwayat Presensi</h1>
            <p class="text-gray-600 dark:text-gray-400">Monitoring dan rekap kehadiran siswa</p>
        </div>

        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8">
                <button wire:click="$set('activeTab', 'riwayat')"
                    class="pb-4 px-1 border-b-2 font-medium {{ $activeTab === 'riwayat' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200' }}">
                    Riwayat
                </button>
                <button wire:click="$set('activeTab', 'rekap')"
                    class="pb-4 px-1 border-b-2 font-medium {{ $activeTab === 'rekap' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200' }}">
                    Rekap
                </button>
            </nav>
        </div>

        @if ($activeTab === 'riwayat')
            {{-- Filter Riwayat --}}
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <select wire:model.live="filters.kelas_id"
                        class="w-full p-3 border border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-600">
                        <option value="">Semua Kelas</option>
                        @foreach ($kelasList as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1">
                    <input type="date" wire:model.live="filters.date"
                        class="w-full p-3 border border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-600">
                </div>
                <button wire:click="loadRiwayat"
                    class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700">Filter</button>
            </div>

            {{-- List Sesi --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($sessions as $session)
                    <div class="bg-white p-6 rounded-xl shadow-lg border hover:shadow-xl transition-all cursor-pointer dark:bg-gray-800 dark:border-gray-700"
                        wire:click="showDetail({{ $session->id }})">
                        <div class="text-sm text-gray-500 mb-1">{{ $session->started_at->format('d M Y') }}</div>
                        <h3 class="text-lg font-bold mb-4 dark:text-white">{{ $session->kelas->name }}</h3>
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                                    {{ $session->presensis->where('status', 'hadir')->count() }}</div>
                                <div class="text-xs text-gray-500">Hadir</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                                    {{ $session->presensis->where('status', 'terlambat')->count() }}</div>
                                <div class="text-xs text-gray-500">Terlambat</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                                    {{ $session->kelas->siswa->count() - $session->presensis->count() }}</div>
                                <div class="text-xs text-gray-500">Tidak Hadir</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400">
                        Belum ada sesi presensi
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            {{ $sessions->links() }}

            {{-- Detail Modal --}}
            @if ($selectedSession)
                <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
                    wire:click="$set('selectedSession', null)">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 max-w-4xl max-h-[90vh] overflow-y-auto shadow-2xl border dark:border-gray-700 w-full"
                        wire:click.stop>
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h2 class="text-2xl font-bold dark:text-white">{{ $selectedSession->kelas->name }}</h2>
                                <p class="text-gray-600 dark:text-gray-400">
                                    {{ $selectedSession->started_at->format('d M Y H:i') }} -
                                    {{ $selectedSession->ended_at?->format('H:i') ?? 'Ongoing' }}</p>
                            </div>
                            <button wire:click="$set('selectedSession', null)"
                                class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div
                            class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 p-6 bg-gray-50 dark:bg-gray-900/30 rounded-xl">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-green-600 dark:text-green-400">
                                    {{ $presensiList->where('status', 'hadir')->count() }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Hadir</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">
                                    {{ $presensiList->where('status', 'terlambat')->count() }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Terlambat</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-red-600 dark:text-red-400">
                                    {{ $selectedSession->kelas->siswa->count() - $presensiList->count() }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Tidak Hadir</div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-700">
                                        <th
                                            class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-300">
                                            Nama</th>
                                        <th
                                            class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-300">
                                            Waktu</th>
                                        <th
                                            class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-300">
                                            Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($presensiList as $presensi)
                                        <tr>
                                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                                {{ $presensi->siswa->name }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $presensi->waktu_scan?->format('H:i') ?? '-' }}</td>
                                            <td class="px-6 py-4">
                                                <span
                                                    class="px-3 py-1 text-xs font-semibold rounded-full {{ $presensi->status === 'hadir' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' }}">
                                                    {{ ucfirst($presensi->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3"
                                                class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                                Belum ada data presensi
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        @if ($activeTab === 'rekap')
            <div class="flex flex-col lg:flex-row gap-4 mb-8">
                <div class="flex-1">
                    <select wire:model.live="filters.kelas_id"
                        class="w-full p-3 border border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-600">
                        <option value="">Semua Kelas</option>
                        @foreach ($kelasList as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 flex gap-2">
                    <input type="date" wire:model="filters.date_start"
                        class="flex-1 p-3 border border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-600">
                    <input type="date" wire:model="filters.date_end"
                        class="flex-1 p-3 border border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-600">
                </div>
                <button wire:click="loadRekap"
                    class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 whitespace-nowrap">Terapkan
                    Filter</button>
            </div>

            <div class="flex justify-end mb-6">
                <button wire:click="exportExcel"
                    class="px-6 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 mr-2 shadow">
                    Export Excel
                </button>
                <button wire:click="exportPdf"
                    class="px-6 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 shadow">
                    Export PDF
                </button>
            </div>

            <div class="bg-white shadow-lg rounded-xl border overflow-hidden dark:bg-gray-800 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-300">
                                    Nama Siswa</th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-300">
                                    Hadir</th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-300">
                                    Terlambat</th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-300">
                                    Tidak Hadir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($rekapData as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                        {{ $row['siswa']->name }}</td>
                                    <td class="px-6 py-4 text-center font-bold text-green-600 dark:text-green-400">
                                        {{ $row['hadir'] }}</td>
                                    <td class="px-6 py-4 text-center font-bold text-yellow-600 dark:text-yellow-400">
                                        {{ $row['terlambat'] }}</td>
                                    <td class="px-6 py-4 text-center font-bold text-red-600 dark:text-red-400">
                                        {{ $row['tidak_hadir'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4"
                                        class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        Pilih kelas dan rentang tanggal untuk melihat rekap
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
