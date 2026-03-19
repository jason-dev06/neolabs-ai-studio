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
        Schema::create('generated_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('prompt');
            $table->string('quality_tier', 20);
            $table->string('duration', 5);
            $table->string('aspect_ratio', 10);
            $table->string('video_style', 30);
            $table->unsignedInteger('credit_cost');
            $table->string('status', 20)->default('pending');
            $table->string('disk', 20)->default('public');
            $table->string('file_path')->nullable();
            $table->string('file_url')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->text('error_message')->nullable();
            $table->uuid('batch_id');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_videos');
    }
};
