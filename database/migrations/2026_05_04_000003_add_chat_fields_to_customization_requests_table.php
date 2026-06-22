<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customization_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('template_id')->nullable()->after('asset_id');
            $table->text('reason')->nullable()->after('description');
            $table->double('final_price')->nullable()->after('price');
            $table->unsignedBigInteger('accepted_price_adjustment_id')->nullable()->after('final_price');
        });
    }

    public function down(): void
    {
        Schema::table('customization_requests', function (Blueprint $table) {
            $table->dropColumn(['template_id', 'reason', 'final_price', 'accepted_price_adjustment_id']);
        });
    }
};
