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
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('price_list_id')->constrained('price_lists')->cascadeOnDelete();
            $table->decimal('price_ars', 12, 2)->nullable();
            $table->decimal('price_usd', 10, 2)->nullable();
            $table->enum('currency_default', ['ARS', 'USD'])->default('ARS');
            $table->timestamps();

            // Un producto tiene un solo precio por lista
            $table->unique(['product_id', 'price_list_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};
