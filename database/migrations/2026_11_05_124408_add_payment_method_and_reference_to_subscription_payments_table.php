<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->string('payment_method', 20)->nullable()->after('status');
            $table->string('reference', 100)->nullable()->after('payment_method');
        });

        DB::table('subscription_payments')
            ->whereNotNull('mp_payment_id')
            ->update(['payment_method' => 'mercadopago']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'reference']);
        });
    }
};
