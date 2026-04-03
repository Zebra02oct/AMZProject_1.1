<?php

namespace Database\Factories;

use App\Models\Kelas;
use App\Models\Mapel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mapel>
 */
class MapelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $namaMapel = fake()->randomElement([
            'Pendidikan Agama',
            'PPKn',
            'Bahasa Indonesia',
            'Matematika',
            'Bahasa Inggris',
            'Informatika',
            'Kewirausahaan',
            'Produktif Kejuruan',
        ]);

        return [
            'kode_mapel' => strtoupper(fake()->unique()->bothify('MPL-###??')),
            'nama_mapel' => $namaMapel,
            'kelas_id' => Kelas::query()->inRandomOrder()->value('id') ?? Kelas::factory(),
        ];
    }
}
