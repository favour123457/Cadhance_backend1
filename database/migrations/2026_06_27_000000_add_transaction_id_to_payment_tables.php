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
        Schema::table('wallet_histories', function (Blueprint $table) {
            $table->string('transaction_id')->nullable()->after('tx_ref');
        });

        Schema::table('user_purchases', function (Blueprint $table) {
            $table->string('transaction_id')->nullable()->after('tx_ref');
        });

        Schema::table('group_subscriptions', function (Blueprint $table) {
            $table->string('transaction_id')->nullable()->after('tx_ref');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_histories', function (Blueprint $table) {
            $table->dropColumn('transaction_id');
        });

        Schema::table('user_purchases', function (Blueprint $table) {
            $table->dropColumn('transaction_id');
        });

        Schema::table('group_subscriptions', function (Blueprint $table) {
            $table->dropColumn('transaction_id');
        });
    }
};
