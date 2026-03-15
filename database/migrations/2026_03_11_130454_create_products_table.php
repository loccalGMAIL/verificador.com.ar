<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('name');
            $table->string('barcode')->index();            // EAN-13 u otro formato
            $table->text('description')->nullable();
            $table->decimal('price_ars', 12, 2)->nullable();
            $table->decimal('price_usd', 10, 2)->nullable();
            $table->enum('currency_default', ['ARS', 'USD'])->default('ARS');
            $table->string('image_path')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Un barcode es único dentro del catálogo de cada comercio
            $table->unique(['store_id', 'barcode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
