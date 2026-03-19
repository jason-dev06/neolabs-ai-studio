<?php

namespace Database\Factories;

use App\Enums\VideoEditSourceType;
use App\Models\User;
use App\Models\VideoEditSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VideoEditSession>
 */
class VideoEditSessionFactory extends Factory
{
    protected $model = VideoEditSession::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'source_type' => VideoEditSourceType::Upload,
            'source_generated_video_id' => null,
            'source_path' => 'video-editor-uploads/1/test-video.mp4',
            'source_url' => '/storage/video-editor-uploads/1/test-video.mp4',
            'current_step' => 0,
            'disk' => 'public',
        ];
    }

    public function fromGenerated(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => VideoEditSourceType::Generated,
        ]);
    }
}
