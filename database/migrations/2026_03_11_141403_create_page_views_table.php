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
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();

            // Qué página se visitó
            $table->string('path', 255);                        // /, /register, /login, /v/abc…

            // Origen del visitante
            $table->text('referer')->nullable();                // URL completa del Referer header
            $table->string('referer_domain', 255)->nullable();  // dominio extraído, p.ej. "google.com"
            $table->string('referer_category', 50)->nullable(); // direct | search | social | other

            // Identificación anónima (sin PII)
            $table->string('ip_hash', 64)->nullable();          // SHA-256 de la IP
            $table->text('user_agent')->nullable();

            $table->timestamps();

            // Índices para las queries del dashboard
            $table->index('created_at');
            $table->index('referer_domain');
            $table->index('path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};
