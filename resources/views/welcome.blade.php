<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Presensi Siswa - SMK Santa Familia Tomohon</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(140deg, #b07a22 0%, #d9a845 45%, #f0cf84 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: #fffaf0;
            border-radius: 12px;
            border: 1px solid #efd9ac;
            box-shadow: 0 22px 60px rgba(90, 55, 7, 0.26);
            padding: 60px 40px;
            text-align: center;
            max-width: 500px;
            width: 100%;
            transition: transform 0.28s ease, box-shadow 0.28s ease;
        }

        .login-container:hover {
            transform: translateY(-4px);
            box-shadow: 0 26px 72px rgba(90, 55, 7, 0.34), 0 0 0 3px rgba(217, 168, 69, 0.16);
        }

        .logo-section {
            margin-bottom: 40px;
        }

        .logo {
            width: 200px;
            height: 200px;
            margin: 0 auto 30px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 10px;
        }

        .school-name {
            font-size: 28px;
            font-weight: 700;
            color: #7a4f16;
            margin-bottom: 10px;
            line-height: 1.2;
        }

        .title-accent {
            width: 88px;
            height: 4px;
            margin: 0 auto 16px;
            border-radius: 999px;
            background: linear-gradient(90deg, #8f4f11 0%, #cf952f 55%, #f0c66f 100%);
            box-shadow: 0 2px 10px rgba(143, 79, 17, 0.25);
        }

        .school-subtitle {
            font-size: 14px;
            color: #8b6a3c;
            margin-bottom: 30px;
        }

        .login-button {
            background: linear-gradient(135deg, #8f4f11 0%, #b97820 55%, #d89932 100%);
            color: #fff8ec;
            border: none;
            padding: 14px 40px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(143, 79, 17, 0.44);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .info-text {
            font-size: 13px;
            color: #9f7b47;
            margin-top: 25px;
        }

        @media (max-width: 640px) {
            .login-container {
                padding: 40px 30px;
            }

            .login-container:hover {
                transform: translateY(-1px);
                box-shadow: 0 14px 34px rgba(90, 55, 7, 0.2), 0 0 0 2px rgba(217, 168, 69, 0.1);
            }

            .school-name {
                font-size: 24px;
            }

            .logo {
                width: 150px;
                height: 150px;
            }
        }

        @media (prefers-color-scheme: dark) {
            body {
                background: linear-gradient(135deg, #5e3c12 0%, #7a4f16 45%, #9f7226 100%);
            }

            .login-container {
                background: #2f210f;
                border-color: #8d662b;
            }

            .login-container:hover {
                box-shadow: 0 24px 68px rgba(0, 0, 0, 0.45), 0 0 0 3px rgba(216, 153, 50, 0.2);
            }

            .logo {
                background: #f8fafc;
                border-color: #cbd5e1;
            }

            .school-name {
                color: #ffd889;
            }

            .school-subtitle,
            .info-text {
                color: #e5c58d;
            }

            .title-accent {
                background: linear-gradient(90deg, #d09a3f 0%, #e6bc69 50%, #f5da9d 100%);
                box-shadow: 0 2px 10px rgba(214, 163, 74, 0.35);
            }

            .login-button {
                background: linear-gradient(135deg, #d6a34a 0%, #e8bf74 100%);
                color: #4f3110;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <!-- Logo Section -->
        <div class="logo-section">
            <div class="logo">
                @if (file_exists(public_path('images/Main-logo.jpg')))
                    <img src="{{ asset('images/Main-logo.jpg') }}" alt="Logo SMK Santa Familia Tomohon">
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        style="width: 80px; height: 80px; color: #d1d5db;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                @endif
            </div>

            <!-- School Name -->
            <h1 class="school-name">SMK Santa Familia Tomohon</h1>
            <div class="title-accent" aria-hidden="true"></div>
            <p class="school-subtitle">Sistem Presensi Siswa Online</p>
        </div>

        <!-- Login Button -->
        <div>
            @auth
                <a href="{{ url('/dashboard') }}" class="login-button">
                    Ke Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="login-button">
                    Sign In
                </a>
            @endauth
        </div>

        <!-- Info Text -->
        <p class="info-text">Masukkan kredensial Anda untuk mengakses sistem</p>
    </div>
</body>

</html>
