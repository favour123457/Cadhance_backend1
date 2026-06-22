<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_purchases', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->string('purchasable_type'); // 'asset' or 'template'
            $table->bigInteger('purchasable_id');
            $table->double('amount_paid')->default(0);
            $table->timestamps();

            // Prevent duplicate purchases
            $table->unique(['user_id', 'purchasable_type', 'purchasable_id'], 'unique_user_purchase');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_purchases');
    }
};
