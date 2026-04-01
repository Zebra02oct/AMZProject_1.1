<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-[#7a4f16] dark:text-[#ffd889]">
                Laporan Presensi
            </h1>
            <p class="mt-2 text-lg text-[#8b6a3c] dark:text-[#e5c58d]">
                Rekap dan statistik kehadiran siswa
            </p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button wire:click="clearFilters"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-[#7a4f16] bg-[#fff3dc] hover:bg-[#f8e9c8] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#b97820] dark:bg-[#4a3618] dark:text-[#ffd889] dark:hover:bg-[#5a401a]">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14" />
                </svg>
                Reset Filter
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white/95 dark:bg-[#3a2a13] shadow-xl ring-1 ring-gray-900/5 rounded-2xl p-8">
        <h2 class="text-xl font-semibold mb-6 text-[#7a4f16] dark:text-[#ffd889]">Filter Data</h2>
        <form wire:submit="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tanggal Dari</label>
                <input type="date" wire:model="dateFrom"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#b97820] focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tanggal Sampai</label>
                <input type="date" wire:model="dateTo"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#b97820] focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Kelas</label>
                <select wire:model="kelasId"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#b97820] focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Semua Kelas</option>
                    @foreach ($kelasList as $kelas)
                        <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end space-x-3">
                <button type="submit"
                    class="w-full bg-[#d89932] hover:bg-[#c98b27] text-[#4f3110] px-6 py-2 rounded-lg shadow-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#b97820] focus:ring-offset-2 transition-colors">
                    Tampilkan Data
                </button>
            </div>
        </form>
    </div>

    {{-- Summary Cards --}}
    @if ($filtersApplied)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div
                class="bg-white/95 dark:bg-[#3a2a13] p-6 rounded-2xl shadow-lg border border-[#ecd6aa] dark:border-[#8d662b]">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-gray-100 dark:bg-gray-700">
                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-7.303a6 6 0 012.262 7.244l-1.5-1.258a3 3 0 00-4.524 0L15 15.647l-1.5-1.258a3 3 0 00-4.524 0L9 15.647l-1.5-1.258a3 3 0 00-4.524 0L3 15.647l-1.5-1.258a3 3 0 00-4.524 0L7.5 20.5z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Siswa</p>
                        <p class="text-2xl font-bold text-[#7a4f16] dark:text-[#ffd889]">
                            {{ number_format($totalSiswa) }}</p>
                    </div>
                </div>
            </div>

            <div
                class="bg-white/95 dark:bg-[#3a2a13] p-6 rounded-2xl shadow-lg border border-[#ecd6aa] dark:border-[#8d662b]">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 dark:bg-green-900/30">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Hadir</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ number_format($hadirCount) }}</p>
                    </div>
                </div>
            </div>

            <div
                class="bg-white/95 dark:bg-[#3a2a13] p-6 rounded-2xl shadow-lg border border-[#ecd6aa] dark:border-[#8d662b]">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900/30">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3.344 2.128M20.618 5.382L18 9l2.618 3.618M9.382 18.618L11 14l-2.618-3.618M5.382 18.618L7 14l-2.618-3.618" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Terlambat</p>
                        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                            {{ number_format($telatCount) }}</p>
                    </div>
                </div>
            </div>

            <div
                class="bg-white/95 dark:bg-[#3a2a13] p-6 rounded-2xl shadow-lg border border-[#ecd6aa] dark:border-[#8d662b]">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 dark:bg-red-900/30">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Tidak Hadir</p>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($absenCount) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white/95 dark:bg-[#3a2a13] shadow-xl ring-1 ring-gray-900/5 rounded-2xl p-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
            <h2 class="text-xl font-semibold text-[#7a4f16] dark:text-[#ffd889]">Data Presensi</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-[#f8e9c8] dark:bg-[#4a3618]">
                    <tr>
                        <th
                            class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Nama Siswa</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Kelas</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Tanggal</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Waktu</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse ($presensi as $item)
                        <tr class="hover:bg-[#fff3dc] dark:hover:bg-[#4a3618]">
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[#7a4f16] dark:text-[#ffd889]">
                                {{ $item->siswa->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-[#7a4f16] dark:text-[#ffd889]">
                                {{ $item->siswa->kelas->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-[#7a4f16] dark:text-[#ffd889]">
                                {{ $item->tanggal?->format('d-m-Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-[#7a4f16] dark:text-[#ffd889]">
                                {{ $item->waktu?->format('H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $status = strtolower($item->status ?? '');
                                    $statusClass = match ($status) {
                                        'hadir'
                                            => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300',
                                        'terlambat'
                                            => 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300',
                                        default => 'bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-300',
                                    };
                                @endphp
                                <span
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClass }}">
                                    {{ ucfirst(str_replace(['hadir', 'terlambat'], ['Hadir', 'Terlambat'], $status)) ?: 'Tidak Hadir' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-[#8b6a3c] dark:text-[#e5c58d]">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 0v6m0-6H9m3 0v6m-3-6H6m3 0h3m-3 0h-3m3 0v6m0-6v6m3-6V6m0 0H9m3 0h3" />
                                </svg>
                                <p class="mt-2 text-sm">Tidak ada data presensi untuk filter yang dipilih.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($presensi->hasPages())
            <div class="mt-8 flex justify-center">
                {{ $presensi->links() }}
            </div>
        @endif
    </div>
</div>
