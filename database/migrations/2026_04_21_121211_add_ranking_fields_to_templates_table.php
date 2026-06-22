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
        Schema::table('templates', function (Blueprint $table) {
            // Admin-controlled priority pin (overrides all ranking)
            $table->boolean('is_pinned')->default(false)->after('template_status_id');
            $table->unsignedTinyInteger('pin_position')->default(0)->after('is_pinned'); // 1 = top, 2 = second, etc.
            // Engagement stats used by ranking algorithm
            $table->unsignedInteger('views')->default(0)->after('pin_position');
            $table->unsignedInteger('saves')->default(0)->after('views');
            $table->unsignedInteger('purchase_count')->default(0)->after('saves');
            $table->decimal('conversion_rate', 8, 4)->default(0)->after('purchase_count');
            $table->unsignedInteger('review_count')->default(0)->after('conversion_rate');
            $table->boolean('has_video')->default(false)->after('review_count');
            $table->boolean('has_sample')->default(false)->after('has_video');
            // Subscription boost tier (0=free,3=basic,6=pro,10=premium)
            $table->unsignedTinyInteger('subscription_boost')->default(0)->after('has_sample');
            // Pre-computed rank score (updated by a scheduled job or on save)
            $table->decimal('rank_score', 12, 4)->default(0)->after('subscription_boost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropColumn([
                'is_pinned', 'pin_position', 'views', 'saves', 'purchase_count',
                'conversion_rate', 'review_count', 'has_video', 'has_sample',
                'subscription_boost', 'rank_score',
            ]);
        });
    }
};
