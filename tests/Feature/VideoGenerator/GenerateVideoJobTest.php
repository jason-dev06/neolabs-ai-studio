<?php

use App\Enums\GenerationStatus;
use App\Jobs\GenerateVideoJob;
use App\Models\GeneratedVideo;
use App\Models\User;
use App\Services\GeminiVideoClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

test('job calls gemini api and stores video on success', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $video = GeneratedVideo::create([
        'user_id' => $user->id,
        'prompt' => 'A cat playing piano',
        'quality_tier' => 'fast',
        'duration' => '8',
        'aspect_ratio' => '16:9',
        'video_style' => 'cinematic',
        'credit_cost' => 30,
        'status' => 'pending',
        'batch_id' => fake()->uuid(),
    ]);

    $client = Mockery::mock(GeminiVideoClient::class);
    $client->shouldReceive('generate')
        ->once()
        ->with('veo-3.1-fast-generate-preview', 'A cat playing piano', 8, '16:9')
        ->andReturn('operations/test-op-123');

    $client->shouldReceive('pollUntilDone')
        ->once()
        ->with('operations/test-op-123')
        ->andReturn([
            'done' => true,
            'response' => [
                'generateVideoResponse' => [
                    'generatedSamples' => [
                        ['video' => ['uri' => 'https://example.com/video.mp4']],
                    ],
                ],
            ],
        ]);

    $client->shouldReceive('downloadVideo')
        ->once()
        ->with('https://example.com/video.mp4')
        ->andReturn('fake-video-content');

    app()->instance(GeminiVideoClient::class, $client);

    (new GenerateVideoJob($video))->handle($client);

    $video->refresh();
    expect($video->status)->toBe(GenerationStatus::Completed);
    expect($video->file_path)->toBe("generated-videos/{$video->id}.mp4");

    Storage::disk('public')->assertExists("generated-videos/{$video->id}.mp4");
});

test('job sets failed status on exception', function () {
    Log::shouldReceive('error')
        ->once()
        ->with('Video generation failed.', Mockery::on(function (array $context) {
            return $context['exception'] === 'Gemini API rate limit exceeded'
                && isset($context['video_id'])
                && $context['prompt'] === 'A failing video'
                && isset($context['trace']);
        }));

    $user = User::factory()->create();
    $video = GeneratedVideo::create([
        'user_id' => $user->id,
        'prompt' => 'A failing video',
        'quality_tier' => 'fast',
        'duration' => '4',
        'aspect_ratio' => '16:9',
        'video_style' => 'cinematic',
        'credit_cost' => 30,
        'status' => 'pending',
        'batch_id' => fake()->uuid(),
    ]);

    $job = new GenerateVideoJob($video);
    $job->failed(new RuntimeException('Gemini API rate limit exceeded'));

    $video->refresh();
    expect($video->status)->toBe(GenerationStatus::Failed);
    expect($video->error_message)->toBe('Gemini API rate limit exceeded');
});

test('job sets failed status when gemini returns no video uri', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $video = GeneratedVideo::create([
        'user_id' => $user->id,
        'prompt' => 'A broken video',
        'quality_tier' => 'standard',
        'duration' => '6',
        'aspect_ratio' => '9:16',
        'video_style' => 'cinematic',
        'credit_cost' => 60,
        'status' => 'pending',
        'batch_id' => fake()->uuid(),
    ]);

    $client = Mockery::mock(GeminiVideoClient::class);
    $client->shouldReceive('generate')
        ->once()
        ->andReturn('operations/test-op-456');

    $client->shouldReceive('pollUntilDone')
        ->once()
        ->andReturn([
            'done' => true,
            'response' => ['generateVideoResponse' => ['generatedSamples' => []]],
        ]);

    app()->instance(GeminiVideoClient::class, $client);

    expect(fn () => (new GenerateVideoJob($video))->handle($client))
        ->toThrow(RuntimeException::class, 'No video URI in Gemini response.');
});
