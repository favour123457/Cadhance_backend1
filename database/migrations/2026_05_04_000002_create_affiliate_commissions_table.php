<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_user_id')->index();
            $table->unsignedBigInteger('referred_user_id')->index();
            $table->unsignedBigInteger('user_subscription_id')->unique();
            $table->unsignedBigInteger('subscription_plan_id')->nullable()->index();
            $table->unsignedBigInteger('admin_data_id')->nullable()->index();
            $table->string('billing_cycle')->default('monthly');
            $table->double('plan_amount')->default(0);
            $table->double('commission_rate')->default(0);
            $table->double('commission_amount')->default(0);
            $table->string('status')->default('approved');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['referrer_user_id', 'created_at']);
            $table->index(['referred_user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_commissions');
    }
};
