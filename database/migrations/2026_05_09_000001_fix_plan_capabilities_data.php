<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * La migración add_capabilities_to_plans_table agregó las columnas has_* con default(false),
     * dejando los planes existentes en producción sin ninguna funcionalidad habilitada.
     * Esta migración aplica los valores correctos por nombre de plan.
     */
    public function up(): void
    {
        $capabilities = [
            'Basic' => [
                'has_import_history' => false,
                'has_basic_stats' => false,
                'has_advanced_stats' => false,
                'has_price_lists' => false,
                'has_customization' => false,
                'has_manual_search' => false,
                'has_branches' => false,
                'has_api' => false,
            ],
            'Standard' => [
                'has_import_history' => true,
                'has_basic_stats' => true,
                'has_advanced_stats' => false,
                'has_price_lists' => false,
                'has_customization' => false,
                'has_manual_search' => false,
                'has_branches' => false,
                'has_api' => false,
            ],
            'Pro' => [
                'has_import_history' => true,
                'has_basic_stats' => true,
                'has_advanced_stats' => true,
                'has_price_lists' => true,
                'has_customization' => true,
                'has_manual_search' => true,
                'has_branches' => false,
                'has_api' => false,
            ],
            'Business' => [
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

        foreach ($capabilities as $name => $values) {
            DB::table('plans')->where('name', $name)->update($values);
        }
    }

    public function down(): void
    {
        DB::table('plans')->update([
            'has_import_history' => false,
            'has_basic_stats' => false,
            'has_advanced_stats' => false,
            'has_price_lists' => false,
            'has_customization' => false,
            'has_manual_search' => false,
            'has_branches' => false,
            'has_api' => false,
        ]);
    }
};
