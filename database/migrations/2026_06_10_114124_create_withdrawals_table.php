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
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->bigInteger('bank_account_id')->nullable();
            $table->bigInteger('mobile_money_account_id')->nullable();
            $table->string('payment_method')->default('bank_transfer'); // 'bank_transfer' or 'mobile_money'
            $table->text('reason')->nullable();
            $table->decimal('amount', 15, 2);
            $table->bigInteger('withdrawal_status_id')->default(1); // 1=pending, 2=processing, 3=completed, 4=failed
            $table->timestamp('processed_at')->nullable();
            $table->bigInteger('processed_by')->nullable(); // admin user id
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
