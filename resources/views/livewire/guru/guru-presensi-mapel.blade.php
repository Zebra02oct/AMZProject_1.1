<div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('livewire:init', () => {
            if (window.__guruPresensiMapelHooksRegistered) {
                return;
            }
            window.__guruPresensiMapelHooksRegistered = true;

            const triggerRender = () => {
                generateQrMapel(0);
                updateTimersMapel();
            };

            Livewire.hook('morph.updated', triggerRender);
            Livewire.hook('message.processed', triggerRender);
            window.addEventListener('render-qr-mapel', triggerRender);
            window.addEventListener('swal-success', (event) => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: event.detail?.message ?? '',
                    timer: 3000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            });
            window.addEventListener('swal-error', (event) => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: event.detail?.message ?? '',
                    timer: 5000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            });
        });

        function generateQrMapel(attempt = 0) {
            const maxRetry = 8;
            const retryDelayMs = 180;

            const qrEl = document.getElementById('qrcode-mapel');
            if (!qrEl || !qrEl.dataset.qr) {
                if (attempt < maxRetry) {
                    setTimeout(() => generateQrMapel(attempt + 1), retryDelayMs);
                }
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

            if (attempt < maxRetry) {
                setTimeout(() => generateQrMapel(attempt + 1), retryDelayMs);
                return;
            }

            console.error('QRCode library tidak ditemukan.');
        }

        function getLocationAndStartMapel(componentId) {
            const component = window.Livewire?.find(componentId);
            if (!component) {
                console.error('Komponen Livewire tidak ditemukan untuk memulai sesi presensi mapel.');
                return;
            }

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        component.set('latitude', position.coords.latitude);
                        component.set('longitude', position.coords.longitude);
                        component.call('startSession');
                    },
                    () => {
                        alert('Gagal mendapatkan lokasi. Pastikan izin lokasi/GPS diizinkan di browser laptopmu.');
                        component.set('latitude', null);
                        component.set('longitude', null);
                        component.call('startSession');
                    }, {
                        enableHighAccuracy: true,
                        timeout: 5000,
                    }
                );
            } else {
                alert('Browser kamu tidak mendukung deteksi lokasi.');
                component.set('latitude', null);
                component.set('longitude', null);
                component.call('startSession');
            }
        }

        let timersMapel = {};

        function updateTimersMapel() {
            Object.values(timersMapel).forEach(clearInterval);
            timersMapel = {};

            const sessionEl = document.getElementById('session-timer-mapel');
            const qrEl = document.getElementById('qr-timer-mapel');
            if (sessionEl) timersMapel.session = setInterval(() => @this.set('sessionCountdown', Math.max(0, @this
                .sessionCountdown - 1)), 1000);
            if (qrEl) timersMapel.qr = setInterval(() => @this.set('qrCountdown', Math.max(0, @this.qrCountdown - 1)),
                1000);
        }

        generateQrMapel();
        updateTimersMapel();
    </script>

    <style>
        #qrcode-mapel canvas {
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, .1);
        }
    </style>

    <div class="p-8 max-w-7xl mx-auto space-y-6">
        @if (!$isSessionActive)
            <div class="text-center py-20">
                <div class="text-6xl mb-6">📚</div>
                <h1 class="text-4xl font-bold text-gray-900 mb-4 dark:text-white">Presensi Mata Pelajaran</h1>
                <p class="text-lg text-gray-500 mb-10 dark:text-gray-400">Pilih mapel yang Anda ampu lalu mulai sesi</p>

                <div class="max-w-xl mx-auto space-y-4">
                    <div class="text-left">
                        <label class="block text-sm font-semibold text-gray-700 mb-2 dark:text-gray-300">Mata
                            Pelajaran</label>
                        <select wire:model="selectedMapelId"
                            class="w-full p-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#b97820] focus:border-[#b97820] dark:bg-gray-800 dark:border-gray-600 dark:text-white transition-all">
                            <option value="">Pilih mata pelajaran...</option>
                            @foreach ($mapelList as $mapel)
                                <option value="{{ $mapel->id }}">{{ $mapel->nama_mapel }} -
                                    {{ $mapel->kelas?->name ?? 'Tanpa Kelas' }}</option>
                            @endforeach
                        </select>
                        @error('selectedMapelId')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button onclick="getLocationAndStartMapel('{{ $this->getId() }}')"
                        class="w-full py-4 bg-gradient-to-r from-[#8f4f11] to-[#b97820] text-white font-semibold rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all">
                        Mulai Presensi Mapel →
                    </button>
                </div>
            </div>
        @else
            <div class="space-y-8">
                <div class="bg-white p-8 rounded-2xl shadow-lg border dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 mb-1 dark:text-white">
                                {{ $activeSession->mapel?->nama_mapel }} - {{ $activeSession->kelas?->name }}
                            </h1>
                            <div class="flex items-center gap-6 text-sm text-gray-600 dark:text-gray-400">
                                <span class="font-semibold text-green-600 dark:text-green-400">Sedang berlangsung</span>
                                <span>Sisa: <span id="session-timer-mapel"
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
                    <div id="qrcode-mapel" data-qr="{{ $qrData }}" class="mx-auto mb-6 max-w-xs"></div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2 dark:text-white">Scan untuk presensi mapel</h2>
                    <p class="text-lg text-[#8b6a3c] dark:text-[#e5c58d]">QR aktif <span
                            id="qr-timer-mapel">{{ gmdate('i:s', $qrCountdown) }}</span></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        class="p-8 rounded-2xl shadow-lg border text-center bg-white/95 dark:bg-[#3a2a13] dark:border-gray-700">
                        <div class="text-4xl mb-2">🟢</div>
                        <h3 class="font-semibold mb-2 text-[#7a4f16] dark:text-[#ffd889]">Hadir</h3>
                        <div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $hadirCount }}</div>
                    </div>
                    <div
                        class="p-8 rounded-2xl shadow-lg border text-center bg-white/95 dark:bg-[#3a2a13] dark:border-gray-700">
                        <div class="text-4xl mb-2">🟡</div>
                        <h3 class="font-semibold mb-2 text-[#7a4f16] dark:text-[#ffd889]">Terlambat</h3>
                        <div class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ $terlambatCount }}
                        </div>
                    </div>
                    <div
                        class="p-8 rounded-2xl shadow-lg border text-center bg-white/95 dark:bg-[#3a2a13] dark:border-gray-700">
                        <div class="text-4xl mb-2">🔴</div>
                        <h3 class="font-semibold mb-2 text-[#7a4f16] dark:text-[#ffd889]">Belum Hadir</h3>
                        <div class="text-3xl font-bold text-red-600 dark:text-red-400">{{ $belumHadirCount }}</div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-2xl shadow-lg border overflow-hidden dark:bg-gray-800 dark:border-gray-700">
                    <div class="p-6 border-b bg-[#f8e9c8] dark:bg-[#4a3618] dark:border-gray-600">
                        <h3 class="text-xl font-semibold text-[#7a4f16] dark:text-[#ffd889]">Siswa Hadir
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
                                        <td class="px-6 py-4 font-medium text-[#7a4f16] dark:text-[#ffd889]">
                                            {{ $p->siswa->name }}</td>
                                        <td class="px-6 py-4 text-sm text-[#8b6a3c] dark:text-[#e5c58d]">
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
                                            class="px-6 py-12 text-center text-[#8b6a3c] dark:text-[#e5c58d]">Belum ada
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
