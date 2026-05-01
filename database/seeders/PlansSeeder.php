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
                'name' => 'Basic',
                'price_usd' => 5.00,
                'max_products' => 2000,
                'max_branches' => 0,
                'max_price_lists' => 0,
                'description' => 'Ideal para comercios pequeños.',
                'featured' => false,
                'sort_order' => 1,
                'has_import_history' => false,
                'has_basic_stats' => false,
                'has_advanced_stats' => false,
                'has_price_lists' => false,
                'has_customization' => false,
                'has_manual_search' => false,
                'has_branches' => false,
                'has_api' => false,
            ],
            [
                'name' => 'Standard',
                'price_usd' => 10.00,
                'max_products' => 5000,
                'max_branches' => 0,
                'max_price_lists' => 0,
                'description' => 'El más elegido por comercios en crecimiento.',
                'featured' => true,
                'sort_order' => 2,
                'has_import_history' => true,
                'has_basic_stats' => true,
                'has_advanced_stats' => false,
                'has_price_lists' => false,
                'has_customization' => false,
                'has_manual_search' => false,
                'has_branches' => false,
                'has_api' => false,
            ],
            [
                'name' => 'Pro',
                'price_usd' => 20.00,
                'max_products' => 10000,
                'max_branches' => 0,
                'max_price_lists' => 999,
                'description' => 'Para comercios con catálogo amplio.',
                'featured' => false,
                'sort_order' => 3,
                'has_import_history' => true,
                'has_basic_stats' => true,
                'has_advanced_stats' => true,
                'has_price_lists' => true,
                'has_customization' => true,
                'has_manual_search' => true,
                'has_branches' => false,
                'has_api' => false,
            ],
            [
                'name' => 'Business',
                'price_usd' => 30.00,
                'max_products' => null,
                'max_branches' => null,
                'max_price_lists' => null,
                'description' => 'Sin límites. Para cadenas y grandes comercios.',
                'featured' => false,
                'sort_order' => 4,
                'has_import_history' => true,
                'has_basic_stats' => true,
                'has_advanced_stats' => true,
                'has_price_lists' => true,
                'has_customization' => true,
                'has_manual_search' => true,
                'has_branches' => true,
                'has_api' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['name' => $plan['name']], $plan);
        }
    }
}
