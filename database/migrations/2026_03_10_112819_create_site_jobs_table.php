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
        Schema::create('site_jobs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->string('title');
            $table->text('description');
            $table->text('location');
            $table->string('deadline');
            $table->double('min_salary');
            $table->double('max_salary');
            $table->string('salary_type');
            $table->text('link');
            $table->string('contact_email');
            $table->text('image');
            $table->bigInteger('site_job_status_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_jobs');
    }
};
