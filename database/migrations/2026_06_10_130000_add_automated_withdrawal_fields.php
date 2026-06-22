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
        // Add currency and Flutterwave fields to bank_accounts
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->bigInteger('currency_id')->nullable()->after('user_id');
            $table->string('bank_code')->nullable()->after('bank_name'); // For direct Flutterwave integration
            $table->string('destination_branch_code')->nullable()->after('account_name'); // For Ghana, Kenya
        });

        // Add currency to mobile_money_accounts
        Schema::table('mobile_money_accounts', function (Blueprint $table) {
            $table->bigInteger('currency_id')->nullable()->after('user_id');
            $table->string('network_code')->nullable()->after('provider'); // Flutterwave network code
        });

        // Add automated withdrawal tracking to withdrawals
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->bigInteger('currency_id')->nullable()->after('amount');
            $table->string('flutterwave_reference')->unique()->nullable()->after('currency_id');
            $table->text('flutterwave_response')->nullable()->after('flutterwave_reference'); // JSON response
            $table->text('failure_reason')->nullable()->after('flutterwave_response');
            $table->boolean('auto_processed')->default(true)->after('failure_reason'); // true = auto, false = manual CSV
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn(['currency_id', 'bank_code', 'destination_branch_code']);
        });

        Schema::table('mobile_money_accounts', function (Blueprint $table) {
            $table->dropColumn(['currency_id', 'network_code']);
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn(['currency_id', 'flutterwave_reference', 'flutterwave_response', 'failure_reason', 'auto_processed']);
        });
    }
};
