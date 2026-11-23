<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Machine;

class MachineSeeder extends Seeder
{
    public function run(): void
    {
        Machine::create([
            'machine_code' => 'RVM001',
            'name' => 'EcoBottle Machine 01',
            'location' => 'Kampus',
            'status' => 'active',
        ]);
    }
}