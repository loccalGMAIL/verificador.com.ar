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
        Schema::table('stores', function (Blueprint $table) {
            $table->string('wholesale_source')->default('percentage')->after('wholesale_discount');
            $table->foreignId('wholesale_custom_field_id')
                ->nullable()
                ->after('wholesale_source')
                ->constrained('product_custom_field_definitions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropForeign(['wholesale_custom_field_id']);
            $table->dropColumn(['wholesale_source', 'wholesale_custom_field_id']);
        });
    }
};
