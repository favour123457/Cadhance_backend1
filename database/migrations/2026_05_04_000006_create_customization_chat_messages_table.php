<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customization_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customization_chat_id');
            $table->unsignedBigInteger('sender_user_id');
            $table->string('message_type')->default('text');
            $table->text('message')->nullable();
            $table->text('attachment')->nullable();
            $table->string('attachment_name')->nullable();
            $table->text('meta')->nullable();
            $table->timestamps();

            $table->index(['customization_chat_id', 'created_at'], 'ccm_chat_id_created_at_index');
            $table->index(['sender_user_id', 'created_at'], 'ccm_sender_id_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customization_chat_messages');
    }
};
