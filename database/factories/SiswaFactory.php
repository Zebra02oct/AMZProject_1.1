<?php

namespace Database\Factories;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Siswa>
 */
class SiswaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'nis' => fake()->unique()->numerify('26######'),
            'kelas_id' => Kelas::query()->inRandomOrder()->value('id') ?? Kelas::factory(),
            'user_id' => User::factory()->siswaBelajar(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
        ];
    }
}
