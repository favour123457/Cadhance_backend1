<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_purchases', function (Blueprint $table) {
            $table->string('currency', 3)->default('USD')->after('amount_paid');
            $table->string('tx_ref', 100)->nullable()->unique()->after('currency');
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending')->after('tx_ref');
        });
    }

    public function down(): void
    {
        Schema::table('user_purchases', function (Blueprint $table) {
            $table->dropColumn(['currency', 'tx_ref', 'status']);
        });
    }
};
