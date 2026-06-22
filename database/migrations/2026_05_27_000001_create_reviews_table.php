<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');          // reviewer
            $table->string('reviewable_type');       // 'asset' or 'template'
            $table->bigInteger('reviewable_id');
            $table->tinyInteger('rating');           // 1–5
            $table->text('comment')->nullable();
            $table->timestamps();

            // One review per user per item
            $table->unique(['user_id', 'reviewable_type', 'reviewable_id'], 'unique_user_review');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
