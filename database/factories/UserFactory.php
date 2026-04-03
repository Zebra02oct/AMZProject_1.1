<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->userName().'@guru.belajar.id',
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'Guru', // default role
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create an admin user.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'Admin',
        ]);
    }

    public function operatorAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Operator Sekolah',
            'email' => 'admin@operator.belajar.id',
            'role' => 'Admin',
            'email_verified_at' => now(),
        ]);
    }

    public function guruBelajar(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'Guru',
            'email' => fake()->unique()->userName().'@guru.belajar.id',
            'email_verified_at' => now(),
        ]);
    }

    public function siswaBelajar(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'Siswa',
            'email' => fake()->unique()->userName().'@siswa.belajar.id',
            'email_verified_at' => now(),
        ]);
    }
}
