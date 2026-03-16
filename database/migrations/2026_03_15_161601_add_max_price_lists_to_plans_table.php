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
            // null = ilimitadas (Business). Basic/Standard = 1, Pro = 2
            $table->unsignedInteger('max_price_lists')
                  ->nullable()
                  ->after('max_branches');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('max_price_lists');
        });
    }
};
