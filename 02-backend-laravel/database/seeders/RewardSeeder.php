<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reward;

class RewardSeeder extends Seeder
{
    public function run(): void
    {
        $rewards = [
            [
                'name' => 'EcoBottle Voucher',
                'description' => 'Voucher belanja EcoBottle.',
                'price' => 500,
                'category' => 'Voucher',
                'image' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Reusable Tumbler',
                'description' => 'Tumbler ramah lingkungan.',
                'price' => 1000,
                'category' => 'Drinkware',
                'image' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Eco Tote Bag',
                'description' => 'Tas belanja ramah lingkungan.',
                'price' => 1500,
                'category' => 'Accessories',
                'image' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Premium Eco Package',
                'description' => 'Paket produk ramah lingkungan.',
                'price' => 2500,
                'category' => 'Package',
                'image' => null,
                'is_active' => true,
            ],
        ];

        foreach ($rewards as $reward) {
            Reward::updateOrCreate(
                ['name' => $reward['name']],
                $reward
            );
        }
    }
}