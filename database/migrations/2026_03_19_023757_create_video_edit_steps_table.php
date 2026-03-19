<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_edit_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('video_edit_sessions')->cascadeOnDelete();
            $table->unsignedInteger('step_number');
            $table->string('tool');
            $table->json('tool_settings')->nullable();
            $table->unsignedInteger('credit_cost');
            $table->string('status')->default('pending');
            $table->string('file_path')->nullable();
            $table->string('file_url')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['session_id', 'step_number']);
            $table->index(['session_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_edit_steps');
    }
};
