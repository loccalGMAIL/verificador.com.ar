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
                'name'            => 'Basic',
                'price_usd'       => 5.00,
                'max_products'    => 2000,
                'max_branches'    => 1,
                'max_price_lists' => 1,
                'description'     => 'Ideal para comercios pequeños.',
                'featured'        => false,
                'sort_order'      => 1,
            ],
            [
                'name'            => 'Standard',
                'price_usd'       => 10.00,
                'max_products'    => 5000,
                'max_branches'    => 3,
                'max_price_lists' => 1,
                'description'     => 'El más elegido por comercios en crecimiento.',
                'featured'        => true,
                'sort_order'      => 2,
            ],
            [
                'name'            => 'Pro',
                'price_usd'       => 20.00,
                'max_products'    => 15000,
                'max_branches'    => 5,
                'max_price_lists' => 2,
                'description'     => 'Para comercios con catálogo amplio.',
                'featured'        => false,
                'sort_order'      => 3,
            ],
            [
                'name'            => 'Business',
                'price_usd'       => 30.00,
                'max_products'    => null,   // ilimitado
                'max_branches'    => null,   // ilimitado
                'max_price_lists' => null,   // ilimitado
                'description'     => 'Sin límites. Para cadenas y grandes comercios.',
                'featured'        => false,
                'sort_order'      => 4,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['name' => $plan['name']], $plan);
        }
    }
}
