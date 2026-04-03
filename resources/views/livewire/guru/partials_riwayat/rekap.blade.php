@php
    $fieldClass =
        'h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-amber-400 dark:focus:ring-amber-900/40';
    $fieldDisabledClass =
        'h-11 w-full rounded-lg border border-gray-300 bg-gray-100 px-3 text-sm text-gray-600 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300';
    $buttonBase = 'inline-flex h-11 items-center justify-center rounded-lg px-4 text-sm font-semibold transition';
    $activeFilterCount = count(
        array_filter(
            [
                $filters['quick_range'] ?? null,
                $filters['tipe_sesi'] ?? null,
                $filters['kelas_id'] ?? null,
                $filters['mapel_id'] ?? null,
                $filters['date_start'] ?? null,
                $filters['date_end'] ?? null,
            ],
            fn($value) => !blank($value),
        ),
    );
@endphp

<div class="space-y-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tipe
                Sesi</label>
            @if ($canAccessHarian)
                <select wire:model.live="filters.tipe_sesi" class="{{ $fieldClass }}">
                    <option value="">Semua Tipe</option>
                    <option value="harian">Harian</option>
                    <option value="mapel">Mata Pelajaran</option>
                </select>
            @else
                <input type="text" value="Mata Pelajaran" class="{{ $fieldDisabledClass }}" disabled>
            @endif
        </div>

        <div>
            <label
                class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Kelas</label>
            <select wire:model.live="filters.kelas_id" class="{{ $fieldClass }}">
                <option value="">Semua Kelas</option>
                @foreach ($kelasList as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label
                class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Mapel</label>
            @if ($showMapelFilter)
                <select wire:model.live="filters.mapel_id" class="{{ $fieldClass }}">
                    <option value="">Semua Mapel</option>
                    @foreach ($mapelList as $mapel)
                        <option value="{{ $mapel->id }}">
                            {{ $mapel->nama_mapel }}{{ $mapel->kelas?->name ? ' - ' . $mapel->kelas->name : '' }}
                        </option>
                    @endforeach
                </select>
            @else
                <input type="text" value="Tidak berlaku untuk presensi harian" class="{{ $fieldDisabledClass }}"
                    disabled>
            @endif
        </div>

        <div>
            <label
                class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Rentang
                Cepat</label>
            <select wire:model.live="filters.quick_range" class="{{ $fieldClass }}">
                <option value="">Manual</option>
                <option value="today">Hari Ini</option>
                <option value="week">1 Minggu</option>
                <option value="month">1 Bulan</option>
                <option value="semester">1 Semester</option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="space-y-3">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <label
                        class="mb-2 block text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Dari</label>
                    <input type="date" wire:model="filters.date_start"
                        @if ($filters['quick_range']) disabled @endif
                        class="{{ $filters['quick_range'] ? $fieldDisabledClass : $fieldClass }}">
                </div>
                <div>
                    <label
                        class="mb-2 block text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Sampai</label>
                    <input type="date" wire:model="filters.date_end"
                        @if ($filters['quick_range']) disabled @endif
                        class="{{ $filters['quick_range'] ? $fieldDisabledClass : $fieldClass }}">
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button wire:click="loadRekap"
                    class="{{ $buttonBase }} bg-[#8f4f11] text-[#fff8ec] hover:bg-[#7b430e]">Terapkan</button>
                <button wire:click="resetFilters"
                    class="{{ $buttonBase }} border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">Reset</button>
            </div>
        </div>

        <div class="flex flex-wrap items-end justify-start gap-3 lg:justify-end">
            <button type="button" onclick="showExportHelp()"
                class="{{ $buttonBase }} border border-gray-300 bg-white font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">Cara
                Export</button>

            @if ($canExportExcel)
                <button wire:click="exportExcel"
                    class="{{ $buttonBase }} bg-green-600 text-white shadow hover:bg-green-700">Export
                    Excel</button>
            @else
                <div onclick="warnExportBlocked(this.querySelector('button'))" class="inline-flex">
                    <button type="button" disabled
                        class="{{ $buttonBase }} cursor-not-allowed bg-green-400 text-white opacity-80">Export
                        Excel</button>
                </div>
            @endif

            <button wire:click="exportPdf"
                class="{{ $buttonBase }} bg-red-600 text-white shadow hover:bg-red-700">Export
                PDF</button>
        </div>
    </div>

    <div
        class="flex items-center justify-between rounded-xl border border-amber-100 bg-amber-50/70 px-4 py-3 dark:border-amber-900/40 dark:bg-amber-900/10">
        <p class="text-sm text-amber-900 dark:text-amber-100">Ringkasan Filter Rekap</p>
        <span
            class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold text-amber-800 shadow-sm dark:bg-gray-900 dark:text-amber-200">
            {{ $activeFilterCount }} Filter Aktif
        </span>
    </div>

    <div
        class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="max-h-[60vh] overflow-auto">
            <table class="w-full">
                <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-700">
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
                            Izin</th>
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
                            <td class="px-6 py-4 text-center font-bold text-purple-600 dark:text-purple-400">
                                {{ $row['izin'] ?? 0 }}</td>
                            <td class="px-6 py-4 text-center font-bold text-gray-700 dark:text-gray-200">
                                {{ $row['tanpa_keterangan'] ?? 0 }}</td>
                            <td class="px-6 py-4 text-center font-bold text-gray-700 dark:text-gray-200">
                                {{ $row['total_sesi'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-[#8b6a3c] dark:text-[#e5c58d]">
                                Pilih kelas dan rentang tanggal untuk melihat rekap
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
