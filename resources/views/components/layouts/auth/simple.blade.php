<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
    <style>
        .auth-gold-bg {
            background: linear-gradient(140deg, #b07a22 0%, #d9a845 45%, #f0cf84 100%);
        }

        .auth-gold-panel {
            background: #fffaf0;
            border: 1px solid #efd9ac;
            box-shadow: 0 22px 60px rgba(90, 55, 7, 0.24);
            border-radius: 1rem;
        }

        .auth-brand-title {
            color: #7a4f16;
        }

        .auth-brand-subtitle {
            color: #8b6a3c;
        }

        .auth-brand-accent {
            width: 88px;
            height: 4px;
            border-radius: 999px;
            background: linear-gradient(90deg, #8f4f11 0%, #cf952f 55%, #f0c66f 100%);
            box-shadow: 0 2px 10px rgba(143, 79, 17, 0.25);
        }

        @media (prefers-color-scheme: dark) {
            .auth-gold-bg {
                background: linear-gradient(135deg, #5e3c12 0%, #7a4f16 45%, #9f7226 100%);
            }

            .auth-gold-panel {
                background: #2f210f;
                border-color: #8d662b;
            }

            .auth-brand-title {
                color: #ffd889;
            }

            .auth-brand-subtitle {
                color: #e5c58d;
            }

            .auth-brand-accent {
                background: linear-gradient(90deg, #d09a3f 0%, #e6bc69 50%, #f5da9d 100%);
                box-shadow: 0 2px 10px rgba(214, 163, 74, 0.35);
            }
        }
    </style>
</head>

<body class="min-h-screen auth-gold-bg antialiased">
    <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
        <div class="w-full max-w-sm auth-gold-panel p-6 md:p-8">
            <div class="mb-6 flex flex-col items-center gap-3 text-center">
                @if (file_exists(public_path('images/logo.png')))
                    <img src="{{ asset('images/logo.png') }}" alt="Logo sekolah"
                        class="h-16 w-16 rounded-md bg-white object-contain p-1" />
                @endif
                <h2 class="text-base font-semibold auth-brand-title">SMK Katolik St. Familia Tomohon</h2>
                <div class="auth-brand-accent" aria-hidden="true"></div>
                <p class="text-xs auth-brand-subtitle">Sistem Presensi Siswa Online</p>
            </div>

            <div class="flex flex-col gap-6">
                {{ $slot }}
            </div>
        </div>
    </div>
    @fluxScripts
</body>

</html>
