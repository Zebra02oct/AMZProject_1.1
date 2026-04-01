<div>
    <div class="max-w-7xl mx-auto p-6 space-y-6">
        <div
            class="rounded-3xl border border-amber-200/70 bg-gradient-to-br from-[#fff7ea] via-white to-[#f8efe0] p-6 shadow-sm dark:border-gray-700 dark:from-[#2a2013] dark:via-[#22190d] dark:to-[#1c140a]">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#a06a1d] dark:text-[#f0c66f]">
                        Laporan Presensi</p>
                    <h1 class="mt-2 text-3xl font-bold text-[#7a4f16] dark:text-[#ffd889]">Riwayat Presensi</h1>
                    <p class="mt-2 max-w-2xl text-gray-600 dark:text-gray-400">Kumpulkan seluruh sesi presensi kelas dan
                        mata pelajaran dalam satu laporan, lalu lihat detail dan rekap per siswa.</p>
                </div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div
                        class="rounded-2xl border border-white/60 bg-white/70 px-4 py-3 shadow-sm dark:border-gray-700 dark:bg-gray-900/60">
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Sesi</div>
                        <div class="mt-1 text-2xl font-bold text-[#7a4f16] dark:text-[#ffd889]">{{ $sessions->total() }}
                        </div>
                    </div>
                    <div
                        class="rounded-2xl border border-white/60 bg-white/70 px-4 py-3 shadow-sm dark:border-gray-700 dark:bg-gray-900/60">
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Kelas</div>
                        <div class="mt-1 text-2xl font-bold text-[#7a4f16] dark:text-[#ffd889]">
                            {{ $kelasList->count() }}</div>
                    </div>
                    <div
                        class="rounded-2xl border border-white/60 bg-white/70 px-4 py-3 shadow-sm dark:border-gray-700 dark:bg-gray-900/60">
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Mapel</div>
                        <div class="mt-1 text-2xl font-bold text-[#7a4f16] dark:text-[#ffd889]">
                            {{ $mapelList->count() }}</div>
                    </div>
                    <div
                        class="rounded-2xl border border-white/60 bg-white/70 px-4 py-3 shadow-sm dark:border-gray-700 dark:bg-gray-900/60">
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Rekap</div>
                        <div class="mt-1 text-2xl font-bold text-[#7a4f16] dark:text-[#ffd889]">{{ count($rekapData) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8">
                <button wire:click="$set('activeTab', 'riwayat')"
                    class="pb-4 px-1 border-b-2 font-medium {{ $activeTab === 'riwayat' ? 'border-[#b97820] text-[#8f4f11] dark:text-[#f0c66f]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200' }}">
                    Riwayat
                </button>
                <button wire:click="$set('activeTab', 'rekap')"
                    class="pb-4 px-1 border-b-2 font-medium {{ $activeTab === 'rekap' ? 'border-[#b97820] text-[#8f4f11] dark:text-[#f0c66f]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200' }}">
                    Rekap
                </button>
            </nav>
        </div>

        @if ($activeTab === 'riwayat')
            <div
                class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="grid gap-4 lg:grid-cols-4">
                    <div>
                        <label
                            class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tipe
                            Sesi</label>
                        @if ($canAccessHarian)
                            <select wire:model.live="filters.tipe_sesi"
                                class="w-full rounded-lg border border-gray-300 p-3 dark:border-gray-600 dark:bg-gray-900">
                                <option value="">Semua Tipe</option>
                                <option value="harian">Harian</option>
                                <option value="mapel">Mata Pelajaran</option>
                            </select>
                        @else
                            <input type="text" value="Mata Pelajaran"
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 p-3 text-gray-600 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                disabled>
                        @endif
                    </div>
                    <div>
                        <label
                            class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Kelas</label>
                        <select wire:model.live="filters.kelas_id"
                            class="w-full rounded-lg border border-gray-300 p-3 dark:border-gray-600 dark:bg-gray-900">
                            <option value="">Semua Kelas</option>
                            @foreach ($kelasList as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if ($showMapelFilter)
                        <div>
                            <label
                                class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Mapel</label>
                            <select wire:model.live="filters.mapel_id"
                                class="w-full rounded-lg border border-gray-300 p-3 dark:border-gray-600 dark:bg-gray-900">
                                <option value="">Semua Mapel</option>
                                @foreach ($mapelList as $mapel)
                                    <option value="{{ $mapel->id }}">{{ $mapel->nama_mapel }}
                                        {{ $mapel->kelas?->name ? ' - ' . $mapel->kelas->name : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div>
                            <label
                                class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Mapel</label>
                            <input type="text" value="Tidak berlaku untuk presensi harian"
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 p-3 text-gray-600 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                disabled>
                        </div>
                    @endif
                    <div>
                        <label
                            class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tanggal</label>
                        <input type="date" wire:model.live="filters.date"
                            class="w-full rounded-lg border border-gray-300 p-3 dark:border-gray-600 dark:bg-gray-900">
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-3">
                    <button wire:click="loadRiwayat"
                        class="rounded-lg bg-[#8f4f11] px-5 py-3 font-semibold text-[#fff8ec] transition hover:bg-[#7b430e]">Terapkan
                        Filter</button>
                    <button wire:click="resetFilters"
                        class="rounded-lg border border-gray-300 bg-white px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">Reset</button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                @forelse($sessions as $session)
                    <div class="group cursor-pointer rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800"
                        wire:click="showDetail({{ $session->id }})">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $session->started_at->format('d M Y') }}</div>
                                <h3 class="mt-1 text-lg font-bold text-[#7a4f16] dark:text-[#ffd889]">
                                    {{ $session->kelas->name }}</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $session->tipe_sesi === 'mapel' ? $session->mapel?->nama_mapel ?? 'Mata pelajaran' : 'Presensi harian wali kelas' }}
                                </p>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <span
                                    class="rounded-full px-3 py-1 text-xs font-semibold {{ $session->tipe_sesi === 'mapel' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300' }}">
                                    {{ ucfirst($session->tipe_sesi) }}
                                </span>
                                @if ($session->mapel)
                                    <span
                                        class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-200">{{ $session->mapel->nama_mapel }}</span>
                                @endif
                            </div>
                        </div>
                        <div
                            class="mt-5 grid grid-cols-3 gap-4 rounded-xl bg-gray-50 p-4 text-center dark:bg-gray-900/50">
                            <div>
                                <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                                    {{ $session->presensis->where('status', 'hadir')->count() }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Hadir</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                                    {{ $session->presensis->where('status', 'terlambat')->count() }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Terlambat</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                                    {{ $session->presensis->where('status', 'tidak_hadir')->count() }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Tidak Hadir</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12 text-[#8b6a3c] dark:text-[#e5c58d]">
                        Belum ada sesi presensi
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            {{ $sessions->links() }}

            {{-- Detail Modal --}}
            @if ($selectedSession)
                <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                    wire:click="closeDetail">
                    <div class="w-full max-w-5xl max-h-[90vh] overflow-y-auto rounded-3xl border border-white/60 bg-white/95 p-8 shadow-2xl dark:border-gray-700 dark:bg-[#3a2a13]"
                        wire:click.stop>
                        <div class="mb-6 flex items-start justify-between gap-4">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-2xl font-bold text-[#7a4f16] dark:text-[#ffd889]">
                                        {{ $selectedSession->kelas->name }}</h2>
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-semibold {{ $selectedSession->tipe_sesi === 'mapel' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300' }}">
                                        {{ ucfirst($selectedSession->tipe_sesi) }}
                                    </span>
                                    @if ($selectedSession->mapel)
                                        <span
                                            class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-200">{{ $selectedSession->mapel->nama_mapel }}</span>
                                    @endif
                                </div>
                                <p class="mt-2 text-gray-600 dark:text-gray-400">
                                    {{ $selectedSession->started_at->format('d M Y H:i') }} -
                                    {{ $selectedSession->ended_at?->format('H:i') ?? 'Ongoing' }}
                                </p>
                            </div>
                            <button type="button" wire:click.stop="closeDetail"
                                class="rounded-full p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div
                            class="mb-8 grid grid-cols-1 gap-6 rounded-2xl bg-gray-50 p-6 dark:bg-gray-900/30 md:grid-cols-3">
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
                                    {{ $presensiList->where('status', 'tidak_hadir')->count() }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Tidak Hadir</div>
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-700">
                            <table class="w-full">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th
                                            class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-300">
                                            Nama</th>
                                        <th
                                            class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-300">
                                            Waktu</th>
                                        <th
                                            class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-300">
                                            Status</th>
                                        <th
                                            class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-300">
                                            Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                    @forelse($presensiList as $presensi)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/70">
                                            <td class="px-6 py-4 font-medium text-[#7a4f16] dark:text-[#ffd889]">
                                                {{ $presensi->siswa->name }}</td>
                                            <td class="px-6 py-4 text-sm text-[#8b6a3c] dark:text-[#e5c58d]">
                                                {{ $presensi->waktu_scan?->format('H:i') ?? '-' }}</td>
                                            <td class="px-6 py-4">
                                                <span
                                                    class="rounded-full px-3 py-1 text-xs font-semibold {{ $presensi->status === 'hadir' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' : ($presensi->status === 'terlambat' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300') }}">
                                                    {{ $presensi->status === 'tidak_hadir' ? 'Tidak Hadir' : ucfirst($presensi->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                @if ($presensi->status === 'tidak_hadir')
                                                    <select
                                                        wire:change="updateKeterangan({{ $presensi->id }}, $event.target.value)"
                                                        class="rounded-lg border border-gray-300 p-2 text-sm dark:border-gray-600 dark:bg-gray-900">
                                                        <option value="tanpa_keterangan" @selected(($presensi->keterangan ?? 'tanpa_keterangan') === 'tanpa_keterangan')>
                                                            Tanpa Keterangan
                                                        </option>
                                                        <option value="sakit" @selected(($presensi->keterangan ?? 'tanpa_keterangan') === 'sakit')>
                                                            Sakit
                                                        </option>
                                                    </select>
                                                @else
                                                    <span class="text-sm text-gray-500 dark:text-gray-400">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4"
                                                class="px-6 py-12 text-center text-[#8b6a3c] dark:text-[#e5c58d]">Belum
                                                ada data presensi</td>
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
            <div
                class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="grid gap-4 lg:grid-cols-4">
                    <div>
                        <label
                            class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tipe
                            Sesi</label>
                        @if ($canAccessHarian)
                            <select wire:model.live="filters.tipe_sesi"
                                class="w-full rounded-lg border border-gray-300 p-3 dark:border-gray-600 dark:bg-gray-900">
                                <option value="">Semua Tipe</option>
                                <option value="harian">Harian</option>
                                <option value="mapel">Mata Pelajaran</option>
                            </select>
                        @else
                            <input type="text" value="Mata Pelajaran"
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 p-3 text-gray-600 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                disabled>
                        @endif
                    </div>
                    <div>
                        <label
                            class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Kelas</label>
                        <select wire:model.live="filters.kelas_id"
                            class="w-full rounded-lg border border-gray-300 p-3 dark:border-gray-600 dark:bg-gray-900">
                            <option value="">Semua Kelas</option>
                            @foreach ($kelasList as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if ($showMapelFilter)
                        <div>
                            <label
                                class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Mapel</label>
                            <select wire:model.live="filters.mapel_id"
                                class="w-full rounded-lg border border-gray-300 p-3 dark:border-gray-600 dark:bg-gray-900">
                                <option value="">Semua Mapel</option>
                                @foreach ($mapelList as $mapel)
                                    <option value="{{ $mapel->id }}">{{ $mapel->nama_mapel }}
                                        {{ $mapel->kelas?->name ? ' - ' . $mapel->kelas->name : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div>
                            <label
                                class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Mapel</label>
                            <input type="text" value="Tidak berlaku untuk presensi harian"
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 p-3 text-gray-600 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                disabled>
                        </div>
                    @endif
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label
                                class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Dari</label>
                            <input type="date" wire:model="filters.date_start"
                                class="w-full rounded-lg border border-gray-300 p-3 dark:border-gray-600 dark:bg-gray-900">
                        </div>
                        <div>
                            <label
                                class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Sampai</label>
                            <input type="date" wire:model="filters.date_end"
                                class="w-full rounded-lg border border-gray-300 p-3 dark:border-gray-600 dark:bg-gray-900">
                        </div>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-3">
                    <button wire:click="loadRekap"
                        class="rounded-lg bg-[#8f4f11] px-5 py-3 font-semibold text-[#fff8ec] transition hover:bg-[#7b430e]">Terapkan
                        Filter</button>
                    <button wire:click="resetFilters"
                        class="rounded-lg border border-gray-300 bg-white px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">Reset</button>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <button wire:click="exportExcel"
                    class="rounded-lg bg-green-600 px-5 py-2 font-semibold text-white shadow transition hover:bg-green-700">
                    Export Excel
                </button>
                <button wire:click="exportPdf"
                    class="rounded-lg bg-red-600 px-5 py-2 font-semibold text-white shadow transition hover:bg-red-700">
                    Export PDF
                </button>
            </div>

            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-300">
                                    Nama Siswa</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-300">
                                    Kelas</th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-300">
                                    Hadir</th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-300">
                                    Terlambat</th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-300">
                                    Tidak Hadir</th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-300">
                                    Sakit</th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-300">
                                    Tanpa Ket.</th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-300">
                                    Total Sesi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($rekapData as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 font-medium text-[#7a4f16] dark:text-[#ffd889]">
                                        {{ $row['siswa']->name }}</td>
                                    <td class="px-6 py-4 text-sm text-[#8b6a3c] dark:text-[#e5c58d]">
                                        {{ $row['siswa']->kelas->name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-center font-bold text-green-600 dark:text-green-400">
                                        {{ $row['hadir'] }}</td>
                                    <td class="px-6 py-4 text-center font-bold text-yellow-600 dark:text-yellow-400">
                                        {{ $row['terlambat'] }}</td>
                                    <td class="px-6 py-4 text-center font-bold text-red-600 dark:text-red-400">
                                        {{ $row['tidak_hadir'] }}</td>
                                    <td class="px-6 py-4 text-center font-bold text-amber-600 dark:text-amber-400">
                                        {{ $row['sakit'] ?? 0 }}</td>
                                    <td class="px-6 py-4 text-center font-bold text-gray-700 dark:text-gray-200">
                                        {{ $row['tanpa_keterangan'] ?? 0 }}</td>
                                    <td class="px-6 py-4 text-center font-bold text-gray-700 dark:text-gray-200">
                                        {{ $row['total_sesi'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6"
                                        class="px-6 py-12 text-center text-[#8b6a3c] dark:text-[#e5c58d]">
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
