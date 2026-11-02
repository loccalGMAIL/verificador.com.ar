<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('odoo_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->unique()->constrained('stores')->cascadeOnDelete();
            $table->string('url');
            $table->string('database', 100);
            $table->text('api_key');                              // Encriptada (cast 'encrypted')
            $table->boolean('enabled')->default(true);
            $table->string('sync_cursor', 30)->nullable();        // Mayor write_date (UTC) ya sincronizado
            $table->timestamp('last_synced_at')->nullable();
            $table->string('last_sync_status', 20)->nullable();   // success | failed
            $table->json('last_sync_stats')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odoo_connections');
    }
};
