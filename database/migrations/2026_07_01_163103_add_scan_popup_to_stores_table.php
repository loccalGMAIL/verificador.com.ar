<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->boolean('scan_popup_enabled')->default(false)->after('invite_token');
            $table->string('scan_popup_image_path')->nullable()->after('scan_popup_enabled');
            $table->unsignedSmallInteger('scan_popup_duration')->default(6)->after('scan_popup_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['scan_popup_enabled', 'scan_popup_image_path', 'scan_popup_duration']);
        });
    }
};
