<?php

use App\Enums\GenerationStatus;
use App\Jobs\ProcessVideoEditJob;
use App\Models\User;
use App\Models\VideoEditSession;
use App\Models\VideoEditStep;
use App\Services\VideoEditor\CaptionService;
use App\Services\VideoEditor\FFmpegService;
use Illuminate\Support\Facades\Storage;

test('local tool (trim) uses FFmpeg and stores output', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $session = VideoEditSession::factory()->create(['user_id' => $user->id]);

    Storage::disk('public')->put($session->source_path, 'fake-video-content');

    $step = VideoEditStep::factory()->create([
        'session_id' => $session->id,
        'step_number' => 1,
        'tool' => 'trim_cut',
        'tool_settings' => ['start_time' => '00:00', 'end_time' => '00:05'],
        'credit_cost' => 5,
        'status' => GenerationStatus::Pending,
    ]);

    $ffmpeg = Mockery::mock(FFmpegService::class);
    $ffmpeg->shouldReceive('process')
        ->once()
        ->withArgs(function ($tool, $inputPath, $outputPath, $settings) {
            return $tool->value === 'trim_cut'
                && str_contains($outputPath, '1.mp4')
                && $settings['start_time'] === '00:00';
        })
        ->andReturnUsing(function ($tool, $inputPath, $outputPath) {
            // Simulate FFmpeg creating the output file
            file_put_contents($outputPath, 'trimmed-video');
        });

    $captions = Mockery::mock(CaptionService::class);
    $captions->shouldNotReceive('generateSrt');

    (new ProcessVideoEditJob($step, $session->source_path))->handle($ffmpeg, $captions);

    $step->refresh();
    $session->refresh();

    expect($step->status)->toBe(GenerationStatus::Completed);
    expect($step->file_path)->toBe("edited-videos/{$session->id}/1.mp4");
    expect($session->current_step)->toBe(1);
});

test('auto_captions generates SRT and burns subtitles via FFmpeg', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $session = VideoEditSession::factory()->create(['user_id' => $user->id]);

    Storage::disk('public')->put($session->source_path, 'fake-video-content');

    $step = VideoEditStep::factory()->create([
        'session_id' => $session->id,
        'step_number' => 1,
        'tool' => 'auto_captions',
        'tool_settings' => ['language' => 'en'],
        'credit_cost' => 10,
        'status' => GenerationStatus::Pending,
    ]);

    $captions = Mockery::mock(CaptionService::class);
    $captions->shouldReceive('generateSrt')
        ->once()
        ->withArgs(function ($videoPath, $language) {
            return $language === 'en';
        })
        ->andReturn("1\n00:00:00,000 --> 00:00:05,000\nHello world\n\n");

    $ffmpeg = Mockery::mock(FFmpegService::class);
    $ffmpeg->shouldReceive('process')
        ->once()
        ->withArgs(function ($tool, $inputPath, $outputPath, $settings) {
            return $tool->value === 'auto_captions'
                && str_contains($outputPath, '1.mp4')
                && isset($settings['srt_path'])
                && $settings['language'] === 'en';
        })
        ->andReturnUsing(function ($tool, $inputPath, $outputPath) {
            file_put_contents($outputPath, 'captioned-video');
        });

    (new ProcessVideoEditJob($step, $session->source_path))->handle($ffmpeg, $captions);

    $step->refresh();

    expect($step->status)->toBe(GenerationStatus::Completed);
    expect($step->file_path)->toBe("edited-videos/{$session->id}/1.mp4");
});

test('ai_effects uses FFmpeg filter processing', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $session = VideoEditSession::factory()->create(['user_id' => $user->id]);

    Storage::disk('public')->put($session->source_path, 'fake-video-content');

    $step = VideoEditStep::factory()->create([
        'session_id' => $session->id,
        'step_number' => 1,
        'tool' => 'ai_effects',
        'tool_settings' => ['effect' => 'cinematic'],
        'credit_cost' => 15,
        'status' => GenerationStatus::Pending,
    ]);

    $captions = Mockery::mock(CaptionService::class);
    $captions->shouldNotReceive('generateSrt');

    $ffmpeg = Mockery::mock(FFmpegService::class);
    $ffmpeg->shouldReceive('process')
        ->once()
        ->withArgs(function ($tool, $inputPath, $outputPath, $settings) {
            return $tool->value === 'ai_effects'
                && str_contains($outputPath, '1.mp4')
                && $settings['effect'] === 'cinematic';
        })
        ->andReturnUsing(function ($tool, $inputPath, $outputPath) {
            file_put_contents($outputPath, 'effects-video');
        });

    (new ProcessVideoEditJob($step, $session->source_path))->handle($ffmpeg, $captions);

    $step->refresh();

    expect($step->status)->toBe(GenerationStatus::Completed);
    expect($step->file_path)->toBe("edited-videos/{$session->id}/1.mp4");
});

test('extend_video uses FFmpeg tpad freeze-frame', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $session = VideoEditSession::factory()->create(['user_id' => $user->id]);

    Storage::disk('public')->put($session->source_path, 'fake-video-content');

    $step = VideoEditStep::factory()->create([
        'session_id' => $session->id,
        'step_number' => 1,
        'tool' => 'extend_video',
        'tool_settings' => ['extend_duration' => '4'],
        'credit_cost' => 20,
        'status' => GenerationStatus::Pending,
    ]);

    $captions = Mockery::mock(CaptionService::class);
    $captions->shouldNotReceive('generateSrt');

    $ffmpeg = Mockery::mock(FFmpegService::class);
    $ffmpeg->shouldReceive('process')
        ->once()
        ->withArgs(function ($tool, $inputPath, $outputPath, $settings) {
            return $tool->value === 'extend_video'
                && str_contains($outputPath, '1.mp4')
                && $settings['extend_duration'] === '4';
        })
        ->andReturnUsing(function ($tool, $inputPath, $outputPath) {
            file_put_contents($outputPath, 'extended-video');
        });

    (new ProcessVideoEditJob($step, $session->source_path))->handle($ffmpeg, $captions);

    $step->refresh();

    expect($step->status)->toBe(GenerationStatus::Completed);
    expect($step->file_path)->toBe("edited-videos/{$session->id}/1.mp4");
});

test('job sets failed status and refunds credits on exception', function () {
    $user = User::factory()->create(['credits' => 95]);
    $session = VideoEditSession::factory()->create(['user_id' => $user->id]);

    $step = VideoEditStep::factory()->create([
        'session_id' => $session->id,
        'step_number' => 1,
        'tool' => 'trim_cut',
        'credit_cost' => 5,
        'status' => GenerationStatus::Processing,
    ]);

    $job = new ProcessVideoEditJob($step, $session->source_path);
    $job->failed(new RuntimeException('Processing error'));

    $step->refresh();
    $user->refresh();

    expect($step->status)->toBe(GenerationStatus::Failed);
    expect($step->error_message)->toBe('Processing error');
    expect($user->credits)->toBe(100); // refunded 5 credits
});
