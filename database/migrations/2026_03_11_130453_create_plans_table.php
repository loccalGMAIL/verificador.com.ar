<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');                        // Basic, Standard, Pro, Business
            $table->decimal('price_usd', 8, 2);
            $table->unsignedInteger('max_products')->nullable(); // null = ilimitado
            $table->text('description')->nullable();
            $table->boolean('featured')->default(false);   // Plan destacado en la landing
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
