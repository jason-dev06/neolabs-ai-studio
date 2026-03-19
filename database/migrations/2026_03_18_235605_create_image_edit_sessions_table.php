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
        Schema::create('image_edit_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('source_type', 20);
            $table->foreignId('source_generated_image_id')
                ->nullable()
                ->constrained('generated_images')
                ->nullOnDelete();
            $table->string('source_path');
            $table->string('source_url');
            $table->unsignedInteger('current_step')->default(0);
            $table->string('disk', 20)->default('public');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('image_edit_sessions');
    }
};
