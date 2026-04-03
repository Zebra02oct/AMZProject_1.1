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

    // function getLocationAndStartMapel(componentId) {
    //     const component = window.Livewire?.find(componentId);
    //     if (!component) {
    //         console.error('Komponen Livewire tidak ditemukan untuk memulai sesi presensi mapel.');
    //         return;
    //     }

    //     if (navigator.geolocation) {
    //         navigator.geolocation.getCurrentPosition(
    //             (position) => {
    //                 component.set('latitude', position.coords.latitude);
    //                 component.set('longitude', position.coords.longitude);
    //                 component.call('startSession');
    //             },
    //             () => {
    //                 alert('Gagal mendapatkan lokasi. Pastikan izin lokasi/GPS diizinkan di browser laptopmu.');
    //                 component.set('latitude', null);
    //                 component.set('longitude', null);
    //             }, {
    //                 enableHighAccuracy: true,
    //                 timeout: 5000,
    //             }
    //         );
    //     } else {
    //         alert('Browser kamu tidak mendukung deteksi lokasi.');
    //         component.set('latitude', null);
    //         component.set('longitude', null);
    //     }
    // }

    function getLocationAndStartMapel(componentId) {
        const component = window.Livewire?.find(componentId);
        if (!component) {
            console.error('Komponen Livewire tidak ditemukan untuk memulai sesi presensi mapel.');
            return;
        }

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    // Jika HTTPS jalan / browser mengizinkan, pakai lokasi asli
                    component.set('latitude', position.coords.latitude);
                    component.set('longitude', position.coords.longitude);
                    component.call('startSession');
                },
                () => {

                    console.log('GPS diblokir/gagal. Menggunakan kordinat bypass Manado (1.4822, 124.8489)');

                    component.set('latitude', 1.273779);
                    component.set('longitude', 124.885567);
                    component.call('startSession');
                }, {
                    enableHighAccuracy: true,
                    timeout: 5000,
                }
            );
        } else {
            // Kalau browsernya jadul banget
            alert('Browser kamu tidak mendukung deteksi lokasi.');
            component.set('latitude', 1.4822);
            component.set('longitude', 124.8489);
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
