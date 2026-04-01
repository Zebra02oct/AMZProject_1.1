<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-[#fff6e6] dark:bg-[#2a1d0d]">
    <flux:sidebar sticky stashable
        class="border-r border-[#e5cb95] bg-[#fffaf0] dark:border-[#8d662b] dark:bg-[#2f210f]">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <a href="{{ route('dashboard') }}" class="mr-5 flex items-center space-x-2" wire:navigate>
            <x-app-logo class="size-8" href="#"></x-app-logo>
        </a>

        @if (auth()->user()?->isAdmin())
            <nav class="space-y-1">
                <h3
                    class="text-xs font-semibold text-[#8b6a3c] dark:text-[#e5c58d] uppercase tracking-wider px-3 mt-6 mb-2">
                    Admin</h3>
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-[#f3dfb7] text-[#7a4f16] dark:bg-[#5a401a] dark:text-[#ffd889]' : 'text-[#8b6a3c] hover:bg-[#f8e9c8] hover:text-[#7a4f16] dark:text-[#e5c58d] dark:hover:bg-[#4a3618]' }}"
                    wire:navigate>
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                        </path>
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.siswa') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.siswa') ? 'bg-[#f3dfb7] text-[#7a4f16] dark:bg-[#5a401a] dark:text-[#ffd889]' : 'text-[#8b6a3c] hover:bg-[#f8e9c8] hover:text-[#7a4f16] dark:text-[#e5c58d] dark:hover:bg-[#4a3618]' }}"
                    wire:navigate>
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                        </path>
                    </svg>
                    Manajemen Siswa
                </a>
                <a href="{{ route('admin.guru') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.guru') ? 'bg-[#f3dfb7] text-[#7a4f16] dark:bg-[#5a401a] dark:text-[#ffd889]' : 'text-[#8b6a3c] hover:bg-[#f8e9c8] hover:text-[#7a4f16] dark:text-[#e5c58d] dark:hover:bg-[#4a3618]' }}"
                    wire:navigate>
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Manajemen Guru
                </a>
                <a href="{{ route('admin.kelas') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.kelas') ? 'bg-[#f3dfb7] text-[#7a4f16] dark:bg-[#5a401a] dark:text-[#ffd889]' : 'text-[#8b6a3c] hover:bg-[#f8e9c8] hover:text-[#7a4f16] dark:text-[#e5c58d] dark:hover:bg-[#4a3618]' }}"
                    wire:navigate>
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-6 0h1m-1 4h1m-1 4h1M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18z">
                        </path>
                    </svg>
                    Manajemen Kelas
                </a>
                <a href="{{ route('admin.mapel') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.mapel') ? 'bg-[#f3dfb7] text-[#7a4f16] dark:bg-[#5a401a] dark:text-[#ffd889]' : 'text-[#8b6a3c] hover:bg-[#f8e9c8] hover:text-[#7a4f16] dark:text-[#e5c58d] dark:hover:bg-[#4a3618]' }}"
                    wire:navigate>
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.483 9.246 5 7.5 5C5.754 5 4.168 5.483 3 6.253v13C4.168 18.483 5.754 18 7.5 18c1.746 0 3.332.483 4.5 1.253m0-13C13.168 5.483 14.754 5 16.5 5c1.746 0 3.332.483 4.5 1.253v13C19.832 18.483 18.246 18 16.5 18c-1.746 0-3.332.483-4.5 1.253">
                        </path>
                    </svg>
                    Manajemen Mapel
                </a>
                <a href="{{ route('admin.presensi') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.presensi') ? 'bg-[#f3dfb7] text-[#7a4f16] dark:bg-[#5a401a] dark:text-[#ffd889]' : 'text-[#8b6a3c] hover:bg-[#f8e9c8] hover:text-[#7a4f16] dark:text-[#e5c58d] dark:hover:bg-[#4a3618]' }}"
                    wire:navigate>
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    Presensi Kelas
                </a>
                <a href="{{ route('admin.laporan') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.laporan') ? 'bg-[#f3dfb7] text-[#7a4f16] dark:bg-[#5a401a] dark:text-[#ffd889]' : 'text-[#8b6a3c] hover:bg-[#f8e9c8] hover:text-[#7a4f16] dark:text-[#e5c58d] dark:hover:bg-[#4a3618]' }}"
                    wire:navigate>
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012 2v2m0 0V9a2 2 0 012 2v2m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                        </path>
                    </svg>
                    Laporan
                </a>
            </nav>
        @endif

        @if (auth()->user()?->isGuru())
            <nav class="space-y-1">
                <h3
                    class="text-xs font-semibold text-[#8b6a3c] dark:text-[#e5c58d] uppercase tracking-wider px-3 mt-6 mb-2">
                    Guru</h3>
                <a href="{{ route('guru.dashboard') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('guru.dashboard') ? 'bg-[#f3dfb7] text-[#7a4f16] dark:bg-[#5a401a] dark:text-[#ffd889]' : 'text-[#8b6a3c] hover:bg-[#f8e9c8] hover:text-[#7a4f16] dark:text-[#e5c58d] dark:hover:bg-[#4a3618]' }}"
                    wire:navigate>
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                        </path>
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('guru.presensi') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('guru.presensi') || request()->routeIs('guru.presensi.create') ? 'bg-[#f3dfb7] text-[#7a4f16] dark:bg-[#5a401a] dark:text-[#ffd889]' : 'text-[#8b6a3c] hover:bg-[#f8e9c8] hover:text-[#7a4f16] dark:text-[#e5c58d] dark:hover:bg-[#4a3618]' }}"
                    wire:navigate>
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    Presensi
                </a>
                <a href="{{ route('guru.presensi-mapel') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('guru.presensi-mapel') ? 'bg-[#f3dfb7] text-[#7a4f16] dark:bg-[#5a401a] dark:text-[#ffd889]' : 'text-[#8b6a3c] hover:bg-[#f8e9c8] hover:text-[#7a4f16] dark:text-[#e5c58d] dark:hover:bg-[#4a3618]' }}"
                    wire:navigate>
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422A12.083 12.083 0 0112 20.055a12.083 12.083 0 01-6.16-9.477L12 14z">
                        </path>
                    </svg>
                    Presensi Mapel
                </a>
                <a href="{{ route('guru.riwayat') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('guru.riwayat') ? 'bg-[#f3dfb7] text-[#7a4f16] dark:bg-[#5a401a] dark:text-[#ffd889]' : 'text-[#8b6a3c] hover:bg-[#f8e9c8] hover:text-[#7a4f16] dark:text-[#e5c58d] dark:hover:bg-[#4a3618]' }}"
                    wire:navigate>
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25 2.25a2.25 2.25 0 002.25-2.25A2.25 2.25 0 0018 18m-8.69-5.64a2.25 2.25 0 01-2.25-2.25A2.25 2.25 0 0110.31 9h2.69a2.25 2.25 0 012.25 2.25 2.25 2.25 0 01-2.25 2.25H10.31z">
                        </path>
                    </svg>
                    Riwayat
                </a>
            </nav>
        @endif

        <flux:spacer />

        <!-- Desktop User Menu -->
        <flux:dropdown position="bottom" align="start">
            <flux:profile :name="auth()->user()->name" :initials="auth()->user()->initials()"
                icon-trailing="chevrons-up-down" />

            <flux:menu class="w-[220px]">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-left text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item href="/settings/profile" icon="cog" wire:navigate>Settings</flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-left text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item href="/settings/profile" icon="cog" wire:navigate>Settings</flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @fluxScripts
</body>

</html>
