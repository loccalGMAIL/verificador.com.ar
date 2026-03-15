<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'         => 'Basic',
                'price_usd'    => 5.00,
                'max_products' => 2000,
                'description'  => 'Ideal para comercios pequeños.',
                'featured'     => false,
                'sort_order'   => 1,
            ],
            [
                'name'         => 'Standard',
                'price_usd'    => 10.00,
                'max_products' => 5000,
                'description'  => 'El más elegido por comercios en crecimiento.',
                'featured'     => true,
                'sort_order'   => 2,
            ],
            [
                'name'         => 'Pro',
                'price_usd'    => 20.00,
                'max_products' => 15000,
                'description'  => 'Para comercios con catálogo amplio.',
                'featured'     => false,
                'sort_order'   => 3,
            ],
            [
                'name'         => 'Business',
                'price_usd'    => 30.00,
                'max_products' => null,
                'description'  => 'Sin límite de productos.',
                'featured'     => false,
                'sort_order'   => 4,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['name' => $plan['name']], $plan);
        }
    }
}
