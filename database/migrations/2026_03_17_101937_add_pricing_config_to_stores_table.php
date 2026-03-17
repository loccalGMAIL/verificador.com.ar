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
            $table->string('excel_col_barcode', 100)->default('codigo');
            $table->string('excel_col_name', 100)->default('nombre');
            $table->string('excel_col_price', 100)->default('precio');
            $table->string('retail_label', 100)->default('Precio');
            $table->boolean('show_wholesale')->default(false);
            $table->string('wholesale_label', 100)->default('Mayorista');
            $table->decimal('wholesale_discount', 5, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([
                'excel_col_barcode',
                'excel_col_name',
                'excel_col_price',
                'retail_label',
                'show_wholesale',
                'wholesale_label',
                'wholesale_discount',
            ]);
        });
    }
};
