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
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="grid gap-4 lg:grid-cols-5">
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
                    class="inline-flex h-11 items-center justify-center rounded-lg bg-[#8f4f11] px-4 text-sm font-semibold text-[#fff8ec] transition hover:bg-[#7b430e]">Terapkan
                    Filter</button>
                <button wire:click="resetFilters"
                    class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">Reset</button>
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
                    <div class="mt-5 grid grid-cols-3 gap-4 rounded-xl bg-gray-50 p-4 text-center dark:bg-gray-900/50">
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
            @include('livewire.guru.partials_riwayat.detail')
        @endif
    @endif

    @if ($activeTab === 'rekap')
        @include('livewire.guru.partials_riwayat.rekap')
    @endif
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function warnExportBlocked(targetButton) {
        if (!targetButton) return;

        targetButton.classList.remove('export-shake');
        void targetButton.offsetWidth;
        targetButton.classList.add('export-shake');

        const message = 'Harus pilih rentang cepat, tipe sesi, dan kelas (dan mapel jika tipe mapel).';
        if (window.Swal) {
            window.Swal.fire({
                icon: 'warning',
                title: 'Filter belum lengkap',
                text: message,
                timer: 2200,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
            return;
        }

        alert(message);
    }

    window.addEventListener('export-filter-required', (event) => {
        const message = event.detail?.message ||
            'Harus pilih rentang cepat, tipe sesi, dan kelas (dan mapel jika tipe mapel).';
        if (window.Swal) {
            window.Swal.fire({
                icon: 'warning',
                title: 'Filter belum lengkap',
                text: message,
                timer: 2200,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
            return;
        }

        alert(message);
    });

    function showExportHelp() {
        const html = `
            <ol style="text-align:left">
                <li>Pilih <strong>Rentang Cepat</strong> (Hari Ini / 1 Minggu / 1 Bulan / 1 Semester).</li>
                <li>Pilih <strong>Tipe Sesi</strong> (Harian / Mata Pelajaran).</li>
                <li>Pilih <strong>Kelas</strong> (wajib).</li>
                <li>Jika Tipe = Mata Pelajaran, pilih <strong>Mapel</strong> juga.</li>
                <li>Klik <strong>Terapkan Filter</strong>.</li>
                <li>Setelah itu klik <strong>Export Excel</strong> untuk mengunduh laporan per-minggu.</li>
            </ol>
        `;

        if (window.Swal) {
            window.Swal.fire({
                title: 'Cara Export Laporan',
                html,
                width: 600,
                showCloseButton: true,
            });
            return;
        }

        alert(
            'Cara export:\n1. Pilih Rentang Cepat\n2. Pilih Tipe Sesi\n3. Pilih Kelas\n4. Jika mapel, pilih mapel\n5. Terapkan Filter\n6. Export Excel'
        );
    }
</script>

<style>
    .export-shake {
        animation: export-shake-keyframes 0.35s ease-in-out;
    }

    @keyframes export-shake-keyframes {
        0% {
            transform: translateX(0);
        }

        20% {
            transform: translateX(-5px);
        }

        40% {
            transform: translateX(5px);
        }

        60% {
            transform: translateX(-4px);
        }

        80% {
            transform: translateX(4px);
        }

        100% {
            transform: translateX(0);
        }
    }
</style>
