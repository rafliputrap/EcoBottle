<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BottleType;

class BottleTypeSeeder extends Seeder
{
    public function run(): void
    {
        BottleType::create([
            'name' => 'Botol PET Kecil',
            'material' => 'PET',
            'volume' => 330,
            'reward' => 100,
            'is_active' => true,
        ]);

        BottleType::create([
            'name' => 'Botol PET Sedang',
            'material' => 'PET',
            'volume' => 600,
            'reward' => 200,
            'is_active' => true,
        ]);

        BottleType::create([
            'name' => 'Botol PET Besar',
            'material' => 'PET',
            'volume' => 1500,
            'reward' => 300,
            'is_active' => true,
        ]);
    }
}
