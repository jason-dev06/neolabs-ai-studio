<?php

namespace Database\Factories;

use App\Enums\GenerationStatus;
use App\Enums\VideoEditorTool;
use App\Models\VideoEditSession;
use App\Models\VideoEditStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VideoEditStep>
 */
class VideoEditStepFactory extends Factory
{
    protected $model = VideoEditStep::class;

    public function definition(): array
    {
        $tool = fake()->randomElement(VideoEditorTool::cases());

        return [
            'session_id' => VideoEditSession::factory(),
            'step_number' => 1,
            'tool' => $tool,
            'tool_settings' => null,
            'credit_cost' => $tool->creditCost(),
            'status' => GenerationStatus::Pending,
            'file_path' => null,
            'file_url' => null,
            'error_message' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GenerationStatus::Completed,
            'file_path' => 'edited-videos/1/1.mp4',
            'file_url' => '/storage/edited-videos/1/1.mp4',
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GenerationStatus::Failed,
            'error_message' => 'AI processing failed.',
        ]);
    }
}
