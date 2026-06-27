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
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->string('recipient_email')->nullable()->after('account_name');
            $table->text('recipient_address')->nullable()->after('recipient_email');
            $table->string('recipient_city')->nullable()->after('recipient_address');
            $table->string('recipient_country')->nullable()->after('recipient_city');
            $table->string('recipient_phone')->nullable()->after('recipient_country');
            $table->string('account_type')->nullable()->after('recipient_phone')->comment('checking, savings, etc.');
            $table->string('routing_number')->nullable()->after('account_type');
            $table->string('swift_code')->nullable()->after('routing_number');
            $table->string('postal_code')->nullable()->after('swift_code');
            $table->string('bank_branch')->nullable()->after('postal_code');
            $table->string('beneficiary_country')->nullable()->after('bank_branch');
            $table->string('sender_id_type')->nullable()->after('beneficiary_country');
            $table->string('sender_id_number')->nullable()->after('sender_id_type');
            $table->string('transfer_purpose_code')->nullable()->after('sender_id_number');
        });

        Schema::table('mobile_money_accounts', function (Blueprint $table) {
            $table->text('recipient_address')->nullable()->after('account_name');
            $table->string('recipient_email')->nullable()->after('recipient_address');
            $table->string('recipient_country')->nullable()->after('recipient_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'recipient_email',
                'recipient_address',
                'recipient_city',
                'recipient_country',
                'recipient_phone',
                'account_type',
                'routing_number',
                'swift_code',
                'postal_code',
                'bank_branch',
                'beneficiary_country',
                'sender_id_type',
                'sender_id_number',
                'transfer_purpose_code',
            ]);
        });

        Schema::table('mobile_money_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'recipient_address',
                'recipient_email',
                'recipient_country',
            ]);
        });
    }
};
