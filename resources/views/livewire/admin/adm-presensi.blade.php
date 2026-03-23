<div class="space-y-8" x-data="presensiComponent()" wire:poll.3s="loadData">
    {{-- Load QRCode Library --}}
    <script>
        (function() {
            if (typeof QRCode === 'undefined') {
                console.log('Loading QRCode library...');
                // Try local first
                var localScript = document.createElement('script');
                localScript.src = '/js/qrcode.min.js';
                localScript.async = false;
                localScript.onload = function() {
                    console.log('QRCode library loaded from local');
                    window.QRCodeLoaded = true;
                };
                localScript.onerror = function() {
                    console.error('Local QRCode failed, trying CDNs...');
                    var script = document.createElement('script');
                    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js';
                    script.async = false;
                    script.onload = function() {
                        console.log('QRCode loaded from CDN');
                        window.QRCodeLoaded = true;
                    };
                    script.onerror = function() {
                        console.error('All QRCode sources failed');
                        window.QRCodeLoaded = false;
                    };
                    document.head.appendChild(script);
                };
                document.head.appendChild(localScript);
            } else {
                console.log('QRCode library already available');
                window.QRCodeLoaded = true;
            }
        })();
    </script>

    {{-- Generate QR Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Kelas Selection & Controls --}}
        <div
            class="lg:col-span-1 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Mulai Sesi Presensi</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pilih Kelas</label>
                    <select wire:model.live="selectedKelasId"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all"
                        @change="generateQrIfSelected()">
                        <option value="">Pilih Kelas</option>
                        @foreach ($kelasList as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($activeSession)
                    <div
                        class="text-center p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                        <div class="text-sm font-medium text-green-800 dark:text-green-200 mb-1">✅ Sesi Aktif</div>
                        <div class="text-xs text-green-700 dark:text-green-300">Berakhir:
                            {{ $activeSession->expired_at->diffForHumans() }}</div>
                    </div>
                    <button wire:click="closeSession"
                        class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium shadow-lg transition-all">
                        ❌ Tutup Sesi
                    </button>
                @else
                    <button wire:click="startSession"
                        class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-4 py-3 rounded-lg font-semibold shadow-xl transition-all duration-200 flex items-center justify-center gap-2 text-lg"
                        :disabled="!$wire.selectedKelasId">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                        Mulai Presensi
                    </button>
                @endif
            </div>
        </div>

        {{-- QR Code Display --}}
        <div
            class="lg:col-span-1 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 text-center">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">QR Code Presensi</h3>
            @if ($qrData)
                <div x-data="qrGenerator('{{ $qrData }}')" class="space-y-4">
                    <canvas id="qrcode"
                        class="mx-auto mb-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-xl border-4 border-dashed border-gray-300 dark:border-gray-600"
                        width="256" height="256">
                    </canvas>
                    <div>
                        <div
                            class="font-mono bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded text-sm text-gray-800 dark:text-gray-200">
                            {{ Str::substr($qrData, 0, 8) }}...
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Scan QR ini untuk presensi</div>
                    </div>
                    <div class="bg-gradient-to-r from-yellow-50 to-orange-50 dark:from-yellow-900/20 rounded-lg p-3">
                        <div class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 mb-1">⏱️ Timer</div>
                        <div class="text-2xl font-mono font-bold text-yellow-600 dark:text-yellow-400"
                            x-text="timeLeftFormatted">
                            05:00
                        </div>
                    </div>
                </div>
            @else
                <div class="h-64 flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                    <svg class="w-20 h-20 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    <p>Pilih kelas dan klik "Mulai Presensi" untuk generate QR</p>
                </div>
            @endif
        </div>

        {{-- Stats Cards --}}
        <div class="lg:col-span-1 grid grid-cols-1 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Statistik Sesi Aktif</h4>
                @if ($activeSession)
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                {{ $currentPresensi->where('status', 'hadir')->count() }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Hadir Tepat Waktu</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                                {{ $currentPresensi->where('status', 'terlambat')->count() }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Terlambat</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-600 dark:text-gray-400">
                                {{ $currentPresensi->count() }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Total Scan</div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        Tidak ada sesi aktif
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Current Presensi Table --}}
    @if ($activeSession && $currentPresensi->isNotEmpty())
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Monitoring Presensi Real-time (Kelas
                    {{ $activeSession->kelas->name ?? '' }})</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                No</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Nama Siswa</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Kelas</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Waktu Scan</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($currentPresensi as $index => $p)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $index + 1 }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $p->siswa->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200 text-xs font-medium rounded-full">
                                        {{ $p->siswa->kelas->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $p->waktu->format('H:i:s') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($p->status == 'hadir')
                                        <span
                                            class="px-3 py-1 bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200 text-xs font-medium rounded-full">
                                            🟢 Hadir
                                        </span>
                                    @else
                                        <span
                                            class="px-3 py-1 bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200 text-xs font-medium rounded-full">
                                            🟡 {{ ucfirst($p->status) }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- History Sessions --}}
    <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-600">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Riwayat Sesi Presensi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th
                            class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Tanggal</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Kelas</th>
                        <th
                            class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Hadir</th>
                        <th
                            class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Terlambat</th>
                        <th
                            class="px-6 py-4 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($historySessions as $session)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $session->started_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-200 text-xs font-medium rounded-full">
                                    {{ $session->kelas->name }}
                                </span>
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-green-600 dark:text-green-400">
                                {{ $session->presensi->where('status', 'hadir')->count() }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-yellow-600 dark:text-yellow-400">
                                {{ $session->presensi->where('status', 'terlambat')->count() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <button
                                    class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 font-medium">
                                    Detail →
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                Belum ada riwayat presensi
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- QR Code Library & Alpine JS --}}
    <script>
        function presensiComponent() {
            return {
                qr: null,
                timeInterval: null,
                init() {
                    this.$wire.on('swal-success', (data) => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            toast: true,
                            position: 'top-end',
                            timer: 3000
                        });
                    });
                    this.$wire.on('swal-error', (data) => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message,
                            toast: true,
                            position: 'top-end',
                            timer: 5000
                        });
                    });
                },
                generateQrIfSelected() {
                    if (this.$wire.selectedKelasId) {
                        this.$wire.startSession();
                    }
                }
            }
        }

        function qrGenerator(data) {
            return {
                data: data,
                timeLeft: 300, // 5 minutes in seconds
                timeInterval: null,
                init() {
                    // Listen for session start event
                    this.$wire.on('qr-session-started', (event) => {
                        this.data = event.qrData;
                        this.generateQR();
                    });

                    // Listen for session end event
                    this.$wire.on('qr-session-ended', () => {
                        this.clearQR();
                    });

                    // Generate QR immediately if data is available
                    if (this.data) {
                        this.generateQR();
                    }
                },
                generateQR(retryCount = 0) {
                    // Clear any existing timer
                    if (this.timeInterval) {
                        clearInterval(this.timeInterval);
                    }

                    // Check if QRCode library is available
                    if (typeof QRCode === 'undefined' || !window.QRCodeLoaded) {
                        console.log('QRCode library not loaded yet, retrying... (' + (retryCount + 1) + '/10)');
                        if (retryCount < 10) {
                            // Retry after a delay
                            setTimeout(() => this.generateQR(retryCount + 1), 1000);
                        } else {
                            console.error('QRCode library failed to load after 10 retries');
                        }
                        return;
                    }

                    console.log('QRCode library is available, generating QR code for:', this.data);
                    const canvas = document.getElementById('qrcode');
                    if (!canvas) {
                        console.error('QR code canvas element not found');
                        return;
                    }

                    try {
                        new QRCode(canvas, {
                            text: this.data,
                            width: 256,
                            height: 256,
                            colorDark: "#000000",
                            colorLight: "#ffffff",
                            correctLevel: QRCode.CorrectLevel.H
                        });
                        console.log('QR Code generated successfully');

                        // Reset timer and start countdown
                        this.timeLeft = 300;
                        this.startTimer();

                        // Reset timer and start countdown
                        this.timeLeft = 300;
                        this.startTimer();
                    } catch (error) {
                        console.error('Error generating QR code:', error);
                    }
                },
                startTimer() {
                    this.timeInterval = setInterval(() => {
                        this.timeLeft--;
                        if (this.timeLeft <= 0) {
                            clearInterval(this.timeInterval);
                            this.timeLeft = 0;
                        }
                    }, 1000);
                },
                timeLeftFormatted() {
                    const minutes = Math.floor(this.timeLeft / 60);
                    const seconds = this.timeLeft % 60;
                    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                },
                clearQR() {
                    // Clear timer
                    if (this.timeInterval) {
                        clearInterval(this.timeInterval);
                        this.timeInterval = null;
                    }

                    // Clear canvas
                    const canvas = document.getElementById('qrcode');
                    if (canvas) {
                        const ctx = canvas.getContext('2d');
                        ctx.clearRect(0, 0, canvas.width, canvas.height);
                    }

                    // Reset data and timer
                    this.data = null;
                    this.timeLeft = 300;
                },
            }
        }
    </script>
</div>
