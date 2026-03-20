<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('qr_scheme', 20)->default('blue')->after('active');
            $table->string('qr_layout', 5)->default('a5')->after('qr_scheme');
            $table->string('qr_headline', 80)->default('Verificá tu precio')->after('qr_layout');
            $table->text('qr_instruction')->nullable()->after('qr_headline');
            $table->boolean('qr_show_logo')->default(true)->after('qr_instruction');
            $table->boolean('qr_show_branch')->default(true)->after('qr_show_logo');
            $table->string('qr_logo_position', 10)->default('center')->after('qr_show_branch');
            $table->string('qr_qr_size', 5)->default('md')->after('qr_logo_position');
            $table->string('qr_headline_size', 5)->default('md')->after('qr_qr_size');
            $table->string('qr_instr_size', 5)->default('md')->after('qr_headline_size');
            $table->string('qr_logo_size', 5)->default('md')->after('qr_instr_size');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn([
                'qr_scheme', 'qr_layout', 'qr_headline', 'qr_instruction',
                'qr_show_logo', 'qr_show_branch', 'qr_logo_position',
                'qr_qr_size', 'qr_headline_size', 'qr_instr_size', 'qr_logo_size',
            ]);
        });
    }
};
