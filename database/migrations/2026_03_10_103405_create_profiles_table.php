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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->text('bio')->nullable();
            $table->boolean('visibility')->default(true);
            $table->bigInteger('design_category_id');
            $table->text('social_links')->nullable();
            $table->bigInteger('primary_role_id');
            $table->string('studio_name')->nullable();
            $table->boolean('is_studio_name_display_name')->default(false);
            $table->text('banner_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
