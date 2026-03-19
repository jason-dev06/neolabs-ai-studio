<?php

namespace Database\Factories;

use App\Enums\ImageEditSourceType;
use App\Models\ImageEditSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImageEditSession>
 */
class ImageEditSessionFactory extends Factory
{
    protected $model = ImageEditSession::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'source_type' => ImageEditSourceType::Upload,
            'source_generated_image_id' => null,
            'source_path' => 'editor-uploads/1/test-image.png',
            'source_url' => '/storage/editor-uploads/1/test-image.png',
            'current_step' => 0,
            'disk' => 'public',
        ];
    }

    public function fromGenerated(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => ImageEditSourceType::Generated,
        ]);
    }
}
