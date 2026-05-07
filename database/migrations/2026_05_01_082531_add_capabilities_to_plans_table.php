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
        Schema::table('plans', function (Blueprint $table) {
            $table->boolean('has_import_history')->default(false);
            $table->boolean('has_basic_stats')->default(false);
            $table->boolean('has_advanced_stats')->default(false);
            $table->boolean('has_price_lists')->default(false);
            $table->boolean('has_customization')->default(false);
            $table->boolean('has_manual_search')->default(false);
            $table->boolean('has_branches')->default(false);
            $table->boolean('has_api')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'has_import_history',
                'has_basic_stats',
                'has_advanced_stats',
                'has_price_lists',
                'has_customization',
                'has_manual_search',
                'has_branches',
                'has_api',
            ]);
        });
    }
};
