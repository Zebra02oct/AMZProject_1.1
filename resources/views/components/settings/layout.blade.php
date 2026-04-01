<div class="flex items-start gap-5 max-md:flex-col">
    @php
        $isProfile = request()->routeIs('settings.profile');
        $isPassword = request()->routeIs('settings.password');
        $isAppearance = request()->routeIs('settings.appearance');
    @endphp

    <div
        class="w-full rounded-2xl border border-amber-200/70 bg-amber-50/80 p-3 shadow-sm dark:border-amber-900/60 dark:bg-amber-950/30 md:w-[240px]">
        <flux:navlist>
            <flux:navlist.item icon="user" href="{{ route('settings.profile') }}" :current="$isProfile"
                class="hover-pop {{ $isProfile ? 'rounded-lg border border-amber-300 bg-gradient-to-r from-amber-100 to-yellow-100 text-amber-900 shadow-sm dark:border-amber-700 dark:from-amber-900/60 dark:to-yellow-900/40 dark:text-amber-100 transition-all duration-200 ease-out motion-reduce:transition-none' : 'rounded-lg text-amber-800 hover:bg-amber-100/70 hover:text-amber-900 dark:text-amber-300 dark:hover:bg-amber-900/40 dark:hover:text-amber-100 transition-all duration-200 ease-out motion-reduce:transition-none' }}"
                wire:navigate>
                Profil
            </flux:navlist.item>
            <flux:navlist.item icon="lock-closed" href="{{ route('settings.password') }}" :current="$isPassword"
                class="hover-pop {{ $isPassword ? 'rounded-lg border border-amber-300 bg-gradient-to-r from-amber-100 to-yellow-100 text-amber-900 shadow-sm dark:border-amber-700 dark:from-amber-900/60 dark:to-yellow-900/40 dark:text-amber-100 transition-all duration-200 ease-out motion-reduce:transition-none' : 'rounded-lg text-amber-800 hover:bg-amber-100/70 hover:text-amber-900 dark:text-amber-300 dark:hover:bg-amber-900/40 dark:hover:text-amber-100 transition-all duration-200 ease-out motion-reduce:transition-none' }}"
                wire:navigate>
                Kata Sandi
            </flux:navlist.item>
            <flux:navlist.item icon="swatch" href="{{ route('settings.appearance') }}" :current="$isAppearance"
                class="hover-pop {{ $isAppearance ? 'rounded-lg border border-amber-300 bg-gradient-to-r from-amber-100 to-yellow-100 text-amber-900 shadow-sm dark:border-amber-700 dark:from-amber-900/60 dark:to-yellow-900/40 dark:text-amber-100 transition-all duration-200 ease-out motion-reduce:transition-none' : 'rounded-lg text-amber-800 hover:bg-amber-100/70 hover:text-amber-900 dark:text-amber-300 dark:hover:bg-amber-900/40 dark:hover:text-amber-100 transition-all duration-200 ease-out motion-reduce:transition-none' }}"
                wire:navigate>
                Tampilan
            </flux:navlist.item>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div
        class="flex-1 self-stretch rounded-2xl border border-amber-200/70 bg-white/90 p-5 shadow-sm dark:border-amber-900/60 dark:bg-neutral-900/80 max-md:pt-6">
        <flux:heading class="text-amber-900 dark:text-amber-100">{{ $heading ?? '' }}</flux:heading>
        <flux:subheading class="text-amber-700/90 dark:text-amber-300/90">{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
