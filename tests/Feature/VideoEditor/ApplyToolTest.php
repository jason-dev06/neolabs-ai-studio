<?php

use App\Enums\GenerationStatus;
use App\Jobs\ProcessVideoEditJob;
use App\Models\User;
use App\Models\VideoEditSession;
use App\Models\VideoEditStep;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

test('a user can apply a tool to their session', function () {
    $user = User::factory()->create(['credits' => 100]);
    $session = VideoEditSession::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('video-editor.apply-tool', $session), [
        'tool' => 'trim_cut',
        'tool_settings' => ['start_time' => '00:00', 'end_time' => '00:05'],
    ]);

    $response->assertRedirect();

    $user->refresh();
    expect($user->credits)->toBe(95); // trim_cut costs 5

    $step = $session->steps()->first();
    expect($step)->not->toBeNull();
    expect($step->step_number)->toBe(1);
    expect($step->status)->toBe(GenerationStatus::Pending);
    expect($step->credit_cost)->toBe(5);

    Queue::assertPushed(ProcessVideoEditJob::class);
});

test('tool must be a valid enum', function () {
    $user = User::factory()->create(['credits' => 100]);
    $session = VideoEditSession::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->post(route('video-editor.apply-tool', $session), [
        'tool' => 'invalid_tool',
    ])->assertSessionHasErrors('tool');
});

test('insufficient credits are rejected', function () {
    $user = User::factory()->create(['credits' => 2]);
    $session = VideoEditSession::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->post(route('video-editor.apply-tool', $session), [
        'tool' => 'trim_cut',
        'tool_settings' => ['start_time' => '00:00', 'end_time' => '00:05'],
    ])->assertSessionHasErrors('credits');

    Queue::assertNothingPushed();
});

test('trim_cut requires start_time and end_time', function () {
    $user = User::factory()->create(['credits' => 100]);
    $session = VideoEditSession::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->post(route('video-editor.apply-tool', $session), [
        'tool' => 'trim_cut',
    ])->assertSessionHasErrors('tool_settings.start_time');
});

test('speed_control requires speed_factor', function () {
    $user = User::factory()->create(['credits' => 100]);
    $session = VideoEditSession::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->post(route('video-editor.apply-tool', $session), [
        'tool' => 'speed_control',
    ])->assertSessionHasErrors('tool_settings.speed_factor');
});

test('speed_control works with valid speed factor', function () {
    $user = User::factory()->create(['credits' => 100]);
    $session = VideoEditSession::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('video-editor.apply-tool', $session), [
        'tool' => 'speed_control',
        'tool_settings' => ['speed_factor' => '2'],
    ]);

    $response->assertRedirect();

    $step = $session->steps()->first();
    expect($step)->not->toBeNull();
    expect($step->tool->value)->toBe('speed_control');
    expect($step->tool_settings['speed_factor'])->toBe('2');

    Queue::assertPushed(ProcessVideoEditJob::class);
});

test('auto_captions requires language', function () {
    $user = User::factory()->create(['credits' => 100]);
    $session = VideoEditSession::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->post(route('video-editor.apply-tool', $session), [
        'tool' => 'auto_captions',
    ])->assertSessionHasErrors('tool_settings.language');
});

test('ai_effects requires effect', function () {
    $user = User::factory()->create(['credits' => 100]);
    $session = VideoEditSession::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->post(route('video-editor.apply-tool', $session), [
        'tool' => 'ai_effects',
    ])->assertSessionHasErrors('tool_settings.effect');
});

test('ai_effects works with valid effect', function () {
    $user = User::factory()->create(['credits' => 100]);
    $session = VideoEditSession::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('video-editor.apply-tool', $session), [
        'tool' => 'ai_effects',
        'tool_settings' => ['effect' => 'vintage'],
    ]);

    $response->assertRedirect();

    $step = $session->steps()->first();
    expect($step)->not->toBeNull();
    expect($step->tool_settings['effect'])->toBe('vintage');

    Queue::assertPushed(ProcessVideoEditJob::class);
});

test('extend_video requires extend_duration', function () {
    $user = User::factory()->create(['credits' => 100]);
    $session = VideoEditSession::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->post(route('video-editor.apply-tool', $session), [
        'tool' => 'extend_video',
    ])->assertSessionHasErrors('tool_settings.extend_duration');
});

test('extend_video works with duration and prompt', function () {
    $user = User::factory()->create(['credits' => 100]);
    $session = VideoEditSession::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('video-editor.apply-tool', $session), [
        'tool' => 'extend_video',
        'tool_settings' => ['extend_duration' => '6', 'prompt' => 'Continue the sunset scene'],
    ]);

    $response->assertRedirect();

    $step = $session->steps()->first();
    expect($step)->not->toBeNull();
    expect($step->tool->value)->toBe('extend_video');
    expect($step->credit_cost)->toBe(20);

    Queue::assertPushed(ProcessVideoEditJob::class);
});

test('cannot apply tool while a step is processing', function () {
    $user = User::factory()->create(['credits' => 100]);
    $session = VideoEditSession::factory()->create(['user_id' => $user->id]);

    VideoEditStep::factory()->create([
        'session_id' => $session->id,
        'step_number' => 1,
        'status' => GenerationStatus::Processing,
    ]);

    $this->actingAs($user)->post(route('video-editor.apply-tool', $session), [
        'tool' => 'trim_cut',
        'tool_settings' => ['start_time' => '00:00', 'end_time' => '00:05'],
    ])->assertSessionHasErrors('tool');
});

test('user cannot apply tool to another users session', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create(['credits' => 100]);
    $session = VideoEditSession::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($other)->post(route('video-editor.apply-tool', $session), [
        'tool' => 'trim_cut',
        'tool_settings' => ['start_time' => '00:00', 'end_time' => '00:05'],
    ])->assertForbidden();
});
