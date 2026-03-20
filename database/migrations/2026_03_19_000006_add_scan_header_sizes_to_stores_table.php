<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('scan_logo_size', 10)->default('md')->after('scan_show_logo');
            $table->string('scan_header_font_size', 10)->default('sm')->after('scan_logo_size');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['scan_logo_size', 'scan_header_font_size']);
        });
    }
};
