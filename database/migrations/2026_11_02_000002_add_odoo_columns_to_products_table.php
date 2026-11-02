<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('source', 20)->nullable()->after('active');           // null = manual/Excel, 'odoo'
            $table->unsignedBigInteger('odoo_product_id')->nullable()->after('source');
            $table->index(['store_id', 'odoo_product_id']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['store_id', 'odoo_product_id']);
            $table->dropColumn(['source', 'odoo_product_id']);
        });
    }
};
