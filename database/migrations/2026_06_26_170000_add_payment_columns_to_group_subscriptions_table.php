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
        Schema::table('group_subscriptions', function (Blueprint $table) {
            $table->decimal('amount_paid', 12, 2)->nullable()->after('subscription_date');
            $table->string('currency', 3)->nullable()->after('amount_paid');
            $table->string('tx_ref')->nullable()->after('currency');
            $table->string('status')->default('pending')->after('tx_ref');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['amount_paid', 'currency', 'tx_ref', 'status']);
        });
    }
};
