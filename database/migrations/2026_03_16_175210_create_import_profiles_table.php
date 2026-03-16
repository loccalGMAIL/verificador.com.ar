<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('name');                  // Ej: "Proveedor Samsung", "Formato propio"
            $table->string('description')->nullable();
            // { "barcode": "codigo_barras", "name": "producto", "price_list_1_ars": "precio_min" }
            $table->json('header_mapping');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_profiles');
    }
};
