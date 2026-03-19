<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('scan_bg_color', 20)->default('#0f172a')->after('wholesale_discount');
            $table->string('scan_accent_color', 20)->default('#34d399')->after('scan_bg_color');
            $table->string('scan_secondary_color', 20)->default('#93c5fd')->after('scan_accent_color');
            $table->enum('scan_card_style', ['dark', 'light'])->default('dark')->after('scan_secondary_color');
            $table->enum('scan_font_size', ['sm', 'md', 'lg', 'xl'])->default('lg')->after('scan_card_style');
            $table->boolean('scan_show_logo')->default(false)->after('scan_font_size');
            $table->string('scan_header_text', 100)->default('Consultá el precio')->after('scan_show_logo');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([
                'scan_bg_color',
                'scan_accent_color',
                'scan_secondary_color',
                'scan_card_style',
                'scan_font_size',
                'scan_show_logo',
                'scan_header_text',
            ]);
        });
    }
};
