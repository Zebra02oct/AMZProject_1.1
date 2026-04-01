<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure kelas test exists
        $kelas = Kelas::firstOrCreate(
            ['name' => '10A'],
            ['nama_kelas' => 'Kelas 10A']
        );

        // Test Siswa User
        $user = User::firstOrCreate(
            ['nis' => '12345'],
            [
                'name' => 'Test Siswa Flutter',
                'email' => 'test.siswa@flutter.test',
                'password' => Hash::make('123456'),
                'role' => 'siswa',
            ]
        );

        // Link Siswa
        Siswa::updateOrCreate(
            ['nis' => '12345'],
            [
                'name' => 'Test Siswa Flutter',
                'kelas_id' => $kelas->id,
                'user_id' => $user->id,
            ]
        );

        // Test Guru if needed
        $guruUser = User::firstOrCreate(
            ['email' => 'guru@test.test'],
            [
                'name' => 'Test Guru',
                'nis' => null,
                'password' => Hash::make('123456'),
                'role' => 'guru',
            ]
        );

        // Output
        $this->command->info('Test accounts seeded:');
        $this->command->info('Siswa - NIS: 12345, Pass: 123456');
        $this->command->info('Guru - Email: guru@test.test, Pass: 123456');
    }
}
