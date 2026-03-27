<div>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.hook('morph.updated', () => {
                generateQr();
                updateTimers();
            });
            Livewire.hook('message.processed', () => {
                generateQr();
                updateTimers();
            });
        });

        function generateQr() {
            const qrEl = document.getElementById('qrcode');
            if (!qrEl || !qrEl.dataset.qr) {
                return;
            }

            const qrText = qrEl.dataset.qr;
            qrEl.innerHTML = '';

            if (window.qrcode && window.qrcode.toCanvas) {
                const canvas = document.createElement('canvas');
                qrEl.appendChild(canvas);

                window.qrcode.toCanvas(canvas, qrText, {
                    width: 280,
                    margin: 1
                }, (error) => {
                    if (error) {
                        console.error('Gagal generate QR (qrcode package):', error);
                    }
                });
                return;
            }

            if (typeof window.QRCode === 'function') {
                new window.QRCode(qrEl, {
                    text: qrText,
                    width: 280,
                    height: 280,
                    colorDark: '#000000',
                    colorLight: '#FFFFFF'
                });
                return;
            }

            console.error(
                'QRCode library tidak ditemukan. Pastikan package qrcode sudah di-import di app.js atau qrcodejs tersedia.'
                );
        }

        let timers = {};

        function updateTimers() {
            Object.values(timers).forEach(clearInterval);
            timers = {};

            const sessionEl = document.getElementById('session-timer');
            const qrEl = document.getElementById('qr-timer');
            if (sessionEl) timers.session = setInterval(() => @this.set('sessionCountdown', Math.max(0, @this
                .sessionCountdown - 1)), 1000);
            if (qrEl) timers.qr = setInterval(() => @this.set('qrCountdown', Math.max(0, @this.qrCountdown - 1)), 1000);
        }

        generateQr();
        updateTimers();
    </script>

    <style>
        #qrcode canvas {
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, .1);
        }
    </style>

    <div class="p-8 max-w-7xl mx-auto space-y-6">
        @if (session('success'))
            <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-xl">✓ {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded-xl">✗ {{ session('error') }}</div>
        @endif

        @if (!$isSessionActive)
            <div class="text-center py-20">
                <div class="text-6xl mb-6">📋</div>
                <h1 class="text-4xl font-bold text-gray-900 mb-4 dark:text-white">Presensi</h1>
                <p class="text-xl text-gray-600 mb-12 dark:text-gray-300">Mulai sesi presensi kelas</p>
                <div class="max-w-md mx-auto space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2 dark:text-gray-300">Pilih
                            Kelas</label>
                        <select wire:model="selectedKelasId"
                            class="w-full p-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white transition-all">
                            <option value="">Pilih kelas...</option>
                            @foreach ($kelasList as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('selectedKelasId')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button wire:click="startSession" {{ !$selectedKelasId ? 'disabled' : '' }}
                        class="w-full py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all disabled:opacity-50">
                        Mulai Presensi →
                    </button>
                </div>
            </div>
        @else
            <div class="space-y-8">
                <div class="bg-white p-8 rounded-2xl shadow-lg border dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 mb-1 dark:text-white">
                                {{ $activeSession->kelas->name }}</h1>
                            <div class="flex items-center gap-6 text-sm text-gray-600 dark:text-gray-400">
                                <span class="font-semibold text-green-600 dark:text-green-400">Sedang berlangsung</span>
                                <span>Sisa: <span id="session-timer"
                                        class="font-mono text-xl font-bold">{{ gmdate('i:s', $sessionCountdown) }}</span></span>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <button wire:click="refreshQr"
                                class="px-6 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                                🔄 Generate QR Baru
                            </button>
                            <button wire:click="closeSession"
                                class="px-6 py-2 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all">
                                Tutup Sesi
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white p-12 rounded-2xl shadow-lg text-center border dark:bg-gray-800 dark:border-gray-700">
                    <div id="qrcode" data-qr="{{ $qrData }}" class="mx-auto mb-6 max-w-xs"></div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2 dark:text-white">Scan untuk presensi</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-300">QR aktif <span
                            id="qr-timer">{{ gmdate('i:s', $qrCountdown) }}</span></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        class="p-8 rounded-2xl shadow-lg border text-center bg-white dark:bg-gray-800 dark:border-gray-700">
                        <div class="text-4xl mb-2">🟢</div>
                        <h3 class="font-semibold mb-2 text-gray-900 dark:text-white">Hadir</h3>
                        <div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $hadirCount }}</div>
                    </div>
                    <div
                        class="p-8 rounded-2xl shadow-lg border text-center bg-white dark:bg-gray-800 dark:border-gray-700">
                        <div class="text-4xl mb-2">🟡</div>
                        <h3 class="font-semibold mb-2 text-gray-900 dark:text-white">Terlambat</h3>
                        <div class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ $terlambatCount }}
                        </div>
                    </div>
                    <div
                        class="p-8 rounded-2xl shadow-lg border text-center bg-white dark:bg-gray-800 dark:border-gray-700">
                        <div class="text-4xl mb-2">🔴</div>
                        <h3 class="font-semibold mb-2 text-gray-900 dark:text-white">Belum Hadir</h3>
                        <div class="text-3xl font-bold text-red-600 dark:text-red-400">{{ $belumHadirCount }}</div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-2xl shadow-lg border overflow-hidden dark:bg-gray-800 dark:border-gray-700">
                    <div class="p-6 border-b bg-gray-50 dark:bg-gray-700/50 dark:border-gray-600">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Siswa Hadir
                            ({{ count($currentPresensi) }})</h3>
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
                                @forelse($currentPresensi as $p)
                                    @php $s = $p->waktu_scan?->diffInSeconds($activeSession->started_at) ?? 999; @endphp
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                            {{ $p->siswa->name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $p->waktu_scan?->format('H:i') ?? '-' }}</td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="px-3 py-1 text-xs font-semibold rounded-full {{ $s <= 300 ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' }}">
                                                {{ $s <= 300 ? 'Hadir' : 'Terlambat' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3"
                                            class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">Belum ada
                                            presensi</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <div wire:poll.3s="loadData" style="display: none;"></div>
    </div>
</div>
