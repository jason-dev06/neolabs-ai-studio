<?php

namespace Database\Factories;

use App\Enums\GenerationStatus;
use App\Enums\ImageEditorTool;
use App\Models\ImageEditSession;
use App\Models\ImageEditStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImageEditStep>
 */
class ImageEditStepFactory extends Factory
{
    protected $model = ImageEditStep::class;

    public function definition(): array
    {
        $tool = fake()->randomElement(ImageEditorTool::cases());

        return [
            'session_id' => ImageEditSession::factory(),
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
            'file_path' => 'edited-images/1/1.png',
            'file_url' => '/storage/edited-images/1/1.png',
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
