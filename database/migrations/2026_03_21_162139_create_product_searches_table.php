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
        Schema::create('product_searches', function (Blueprint $table) {
            $table->id();

            // Qué sucursal recibió la consulta
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            // Producto encontrado (null si no se encontró)
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            // Código de barras consultado
            $table->string('barcode', 100);

            // ¿Se encontró el producto?
            $table->boolean('found')->default(false);

            $table->timestamps();

            // Índices para las queries del dashboard
            $table->index('branch_id');
            $table->index('product_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_searches');
    }
};
