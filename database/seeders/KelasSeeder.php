<?php

namespace Database\Seeders;

use App\Models\Kelas;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    public function run(): void
    {
        Kelas::create(['name' => '10 IPA 1']);
        Kelas::create(['name' => '10 IPA 2']);
        Kelas::create(['name' => '11 IPS 1']);
    }
}
