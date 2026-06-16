<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('promo_image_path')->nullable()->after('qr_header_color');
            $table->string('promo_show_when', 20)->nullable()->after('promo_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['promo_image_path', 'promo_show_when']);
        });
    }
};
