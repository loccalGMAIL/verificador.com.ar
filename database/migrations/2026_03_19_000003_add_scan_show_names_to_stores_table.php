<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->boolean('scan_show_store_name')->default(true)->after('scan_header_text');
            $table->boolean('scan_show_branch_name')->default(true)->after('scan_show_store_name');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['scan_show_store_name', 'scan_show_branch_name']);
        });
    }
};
