<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['price_ars', 'price_usd', 'currency_default']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price_ars', 12, 2)->nullable()->after('price');
            $table->decimal('price_usd', 10, 2)->nullable()->after('price_ars');
            $table->string('currency_default', 3)->default('ARS')->after('price_usd');
        });
    }
};
