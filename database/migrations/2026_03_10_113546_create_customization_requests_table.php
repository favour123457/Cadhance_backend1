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
        Schema::create('customization_requests', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('asset_id');
            $table->bigInteger('user_id');
            $table->bigInteger('designer_id');
            $table->text('description');
            $table->double('price');
            $table->bigInteger('customization_status_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customization_requests');
    }
};
