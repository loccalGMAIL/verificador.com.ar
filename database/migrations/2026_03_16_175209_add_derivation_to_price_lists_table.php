<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('price_lists', function (Blueprint $table) {
            // Lista base de la que se deriva (null = lista manual)
            $table->foreignId('base_price_list_id')
                  ->nullable()
                  ->after('sort_order')
                  ->constrained('price_lists')
                  ->nullOnDelete();

            // Porcentaje de ajuste: -20 = descuento 20%, +15 = recargo 15%
            $table->decimal('adjustment_pct', 8, 2)
                  ->nullable()
                  ->after('base_price_list_id')
                  ->comment('Porcentaje de ajuste respecto a la lista base. Ej: -20 = -20%, +15 = +15%');
        });
    }

    public function down(): void
    {
        Schema::table('price_lists', function (Blueprint $table) {
            $table->dropForeign(['base_price_list_id']);
            $table->dropColumn(['base_price_list_id', 'adjustment_pct']);
        });
    }
};
