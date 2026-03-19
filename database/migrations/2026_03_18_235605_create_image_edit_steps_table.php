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
        Schema::create('image_edit_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')
                ->constrained('image_edit_sessions')
                ->cascadeOnDelete();
            $table->unsignedInteger('step_number');
            $table->string('tool', 30);
            $table->json('tool_settings')->nullable();
            $table->unsignedInteger('credit_cost');
            $table->string('status', 20)->default('pending');
            $table->string('file_path')->nullable();
            $table->string('file_url')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['session_id', 'step_number']);
            $table->index(['session_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('image_edit_steps');
    }
};
