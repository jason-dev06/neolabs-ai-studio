<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_edit_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('source_type');
            $table->foreignId('source_generated_video_id')->nullable()->constrained('generated_videos')->nullOnDelete();
            $table->string('source_path');
            $table->string('source_url');
            $table->unsignedInteger('current_step')->default(0);
            $table->string('disk')->default('public');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_edit_sessions');
    }
};
