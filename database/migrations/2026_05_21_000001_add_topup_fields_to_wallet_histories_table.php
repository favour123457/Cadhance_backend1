<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_histories', function (Blueprint $table) {
            $table->string('tx_ref')->nullable()->unique()->after('amount');
            $table->string('currency', 10)->nullable()->after('tx_ref');
        });
    }

    public function down(): void
    {
        Schema::table('wallet_histories', function (Blueprint $table) {
            $table->dropColumn(['tx_ref', 'currency']);
        });
    }
};
