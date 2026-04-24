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
        Schema::table('products', function (Blueprint $table) {
            $table->string('barcode')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Replace existing NULLs with empty string before removing nullable
        DB::table('products')->whereNull('barcode')->update(['barcode' => '']);

        Schema::table('products', function (Blueprint $table) {
            $table->string('barcode')->nullable(false)->change();
        });
    }
};
