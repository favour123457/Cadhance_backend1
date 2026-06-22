<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customization_revisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customization_request_id');
            $table->unsignedBigInteger('requested_by_user_id');
            $table->text('note')->nullable();
            $table->text('attachment')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('status')->default('pending');
            $table->text('decision_reason')->nullable();
            $table->unsignedBigInteger('responded_by_user_id')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['customization_request_id', 'created_at'], 'cr_request_id_created_at_index');
            $table->index(['requested_by_user_id', 'created_at'], 'cr_requester_id_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customization_revisions');
    }
};
