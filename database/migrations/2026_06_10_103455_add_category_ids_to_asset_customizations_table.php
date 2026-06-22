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
        Schema::table('asset_customizations', function (Blueprint $table) {
            $table->json('category_ids')->nullable()->after('designer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_customizations', function (Blueprint $table) {
            $table->dropColumn('category_ids');
        });
    }
};
