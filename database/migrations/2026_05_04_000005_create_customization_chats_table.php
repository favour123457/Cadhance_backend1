<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customization_chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customization_request_id')->unique();
            $table->unsignedBigInteger('asset_id')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->unsignedBigInteger('client_user_id');
            $table->unsignedBigInteger('owner_user_id');
            $table->string('title');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index(['client_user_id', 'last_message_at']);
            $table->index(['owner_user_id', 'last_message_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customization_chats');
    }
};
