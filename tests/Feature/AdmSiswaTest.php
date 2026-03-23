<?php

use App\Models\User;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Livewire\Admin\AdmSiswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('siswa', [
        'name' => 'John Doe',
        'nis' => '12345',
        'kelas_id' => $kelas->id,
    ]);
});
