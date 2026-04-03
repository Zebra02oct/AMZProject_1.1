<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminSeeder extends Seeder
{
    /**
     * Seed the application's admin user.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@example.com');
        $password = env('ADMIN_PASSWORD', 'admin123');
        $name = env('ADMIN_NAME', 'Admin');

        $attributes = [
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'Admin',
        ];

        if (Schema::hasColumn('users', 'nis')) {
            $attributes['nis'] = null;
        }

        User::updateOrCreate(
            ['email' => $email],
            $attributes
        );

        $this->command?->info("Admin seeded: {$email}");
    }
}
