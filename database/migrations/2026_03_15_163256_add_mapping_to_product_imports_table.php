<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_imports', function (Blueprint $table) {
            // Mapeo de columnas: { "barcode": 0, "name": 1, "price_ars": 3, ... }
            $table->json('mapping')->nullable()->after('file_name');
            // Lista de precios destino (nullable = lista default del store)
            $table->foreignId('price_list_id')
                  ->nullable()
                  ->after('mapping')
                  ->constrained('price_lists')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_imports', function (Blueprint $table) {
            $table->dropForeign(['price_list_id']);
            $table->dropColumn(['mapping', 'price_list_id']);
        });
    }
};
