<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customization_price_adjustments', function (Blueprint $table) {
            $table->unsignedBigInteger('requested_by_user_id')->nullable()->after('customization_request_id');
            $table->string('status')->default('pending')->after('reason');
            $table->boolean('is_final')->default(false)->after('status');
            $table->text('decision_reason')->nullable()->after('is_final');
            $table->unsignedBigInteger('responded_by_user_id')->nullable()->after('decision_reason');
            $table->timestamp('responded_at')->nullable()->after('responded_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('customization_price_adjustments', function (Blueprint $table) {
            $table->dropColumn([
                'requested_by_user_id',
                'status',
                'is_final',
                'decision_reason',
                'responded_by_user_id',
                'responded_at',
            ]);
        });
    }
};
