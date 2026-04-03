<?php

use App\Livewire\Admin\AdmSiswa;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('can create siswa', function () {
    $admin = User::factory()->admin()->create();
    $kelas = Kelas::factory()->create();

    Livewire::actingAs($admin)
        ->test(AdmSiswa::class)
        ->set('name', 'John Doe')
        ->set('nis', '12345')
        ->set('kelas_id', $kelas->id)
        ->set('password', 'rahasia123')
        ->set('password_confirmation', 'rahasia123')
        ->call('save')
        ->assertHasNoErrors();

    expect(DB::table('siswa')->where([
        'name' => 'John Doe',
        'nis' => '12345',
        'kelas_id' => $kelas->id,
    ])->exists())->toBeTrue();

    expect(DB::table('users')->where([
        'name' => 'John Doe',
        'email' => '12345@siswa.local',
        'role' => 'Siswa',
    ])->exists())->toBeTrue();

    $siswa = Siswa::where('nis', '12345')->first();
    expect($siswa)->not->toBeNull();
    expect($siswa->user_id)->not->toBeNull();

    $linkedUser = User::find($siswa->user_id);
    expect($linkedUser)->not->toBeNull();
    expect(Hash::check('rahasia123', $linkedUser->password))->toBeTrue();

    if (Schema::hasColumn('users', 'nis')) {
        expect(DB::table('users')->where([
            'id' => $siswa->user_id,
            'nis' => '12345',
        ])->exists())->toBeTrue();
    }
});

test('delete siswa also deletes linked user', function () {
    $admin = User::factory()->admin()->create();
    $kelas = Kelas::factory()->create();

    $userPayload = [
        'name' => 'To Delete',
        'email' => '54321@siswa.local',
        'password' => bcrypt('123456'),
        'role' => 'Siswa',
    ];

    if (Schema::hasColumn('users', 'nis')) {
        $userPayload['nis'] = '54321';
    }

    $linkedUser = User::create($userPayload);

    $siswa = Siswa::create([
        'name' => 'To Delete',
        'nis' => '54321',
        'kelas_id' => $kelas->id,
        'user_id' => $linkedUser->id,
    ]);

    Livewire::actingAs($admin)
        ->test(AdmSiswa::class)
        ->call('deleteConfirm', $siswa->id)
        ->call('delete')
        ->assertHasNoErrors();

    expect(Siswa::find($siswa->id))->toBeNull();
    expect(User::find($linkedUser->id))->toBeNull();
});
