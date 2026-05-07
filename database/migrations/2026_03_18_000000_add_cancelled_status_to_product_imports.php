<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support MODIFY COLUMN or ENUM — enforcement is at application level
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE product_imports MODIFY status ENUM('pending','processing','completed','failed','cancelled') DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE product_imports MODIFY status ENUM('pending','processing','completed','failed') DEFAULT 'pending'");
        }
    }
};
