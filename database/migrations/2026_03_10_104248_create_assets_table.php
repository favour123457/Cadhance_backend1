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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->string('title');
            $table->text('description');
            $table->double('price');
            $table->double('service_charge')->default(0);
            $table->bigInteger('design_category_id');
            $table->integer('favorite_count')->default(0);
            $table->integer('download_count')->default(0);
            $table->integer('view_count')->default(0);
            $table->text('thumbnail');
            $table->bigInteger('bought_count')->default(0);
            $table->string('unique_code');
            $table->double('rating')->default(0);
            $table->string('tools_used')->nullable();
            $table->string('available_file_formats')->nullable();
            $table->bigInteger('license_type_id');
            $table->text('detail_view');
            $table->text('specifications')->nullable();
            $table->boolean('visibility')->default(true);
            $table->boolean('affiliate_settings')->default(false);
            $table->double('affiliate_commission_rate')->default(0);
            $table->boolean('customization_available')->default(false);
            $table->double('customization_price')->default(0);
            $table->bigInteger('asset_status_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
