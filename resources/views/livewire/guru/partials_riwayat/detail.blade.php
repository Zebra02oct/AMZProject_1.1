 <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" wire:click="closeDetail">
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
                                                    <option value="izin" @selected(($presensi->keterangan ?? 'tanpa_keterangan') === 'izin')>
                                                        Izin
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