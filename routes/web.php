<?php

use App\Livewire\Admin\AdmGuru;
use App\Livewire\Admin\AdminDashboard;
use App\Livewire\Admin\AdmKelas;
use App\Livewire\Admin\AdmLaporan;
use App\Livewire\Admin\AdmMapel;
use App\Livewire\Admin\AdmPresensi;
use App\Livewire\Admin\AdmSiswa;
use App\Livewire\Guru\GuruDashboard;
use App\Livewire\Guru\GuruPresensi;
use App\Livewire\Guru\GuruPresensiMapel;
use App\Livewire\Guru\GuruRiwayat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('dashboard', function () {
    $user = Auth::user();

    if ($user?->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    if ($user?->isGuru()) {
        return redirect()->route('guru.dashboard');
    }

    abort(403, 'Role pengguna tidak dikenali.');
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::middleware(['check.admin'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('/', fn () => redirect()->route('admin.dashboard'));
            Route::get('/dashboard', AdminDashboard::class)->name('dashboard');
            Route::get('/siswa', AdmSiswa::class)->name('siswa');
            Route::get('/guru', AdmGuru::class)->name('guru');
            Route::get('/kelas', AdmKelas::class)->name('kelas');
            Route::get('/mapel', AdmMapel::class)->name('mapel');
            Route::get('/presensi', AdmPresensi::class)->name('presensi');
            Route::get('/laporan', AdmLaporan::class)->name('laporan');

            // Route::post('/scan-qr', [App\Http\Controllers\Api\PresensiController::class, 'scan'])->name('scan-qr');
        });

    Route::middleware(['check.guru'])
        ->prefix('guru')
        ->name('guru.')
        ->group(function () {
            Route::get('/dashboard', GuruDashboard::class)->name('dashboard');
            Route::get('/presensi', GuruPresensi::class)->name('presensi');
            Route::get('/presensi-mapel', GuruPresensiMapel::class)->name('presensi-mapel');
            Route::get('/riwayat', GuruRiwayat::class)->name('riwayat');
            Route::get('/presensi/create/{kelas}', fn ($kelas) => redirect()->route('guru.presensi'))->name('presensi.create');
        });

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
