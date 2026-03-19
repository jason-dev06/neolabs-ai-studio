<?php

use App\Enums\ImageEditorTool;

test('all tools have a credit cost', function () {
    foreach (ImageEditorTool::cases() as $tool) {
        expect($tool->creditCost())->toBeGreaterThan(0);
    }
});

test('all tools have a label', function () {
    foreach (ImageEditorTool::cases() as $tool) {
        expect($tool->label())->not->toBeEmpty();
    }
});

test('all tools have a description', function () {
    foreach (ImageEditorTool::cases() as $tool) {
        expect($tool->description())->not->toBeEmpty();
    }
});

test('all tools generate a prompt', function () {
    foreach (ImageEditorTool::cases() as $tool) {
        $settings = match ($tool) {
            ImageEditorTool::Inpaint => ['prompt' => 'Add a hat'],
            ImageEditorTool::EraseObject => ['erase_prompt' => 'Remove the car'],
            ImageEditorTool::StyleTransfer => ['style' => 'watercolor'],
            ImageEditorTool::Extend => ['direction' => 'up'],
            ImageEditorTool::Upscale => ['scale_factor' => 4],
            default => [],
        };

        expect($tool->prompt($settings))->not->toBeEmpty();
    }
});

test('credit costs match expected values', function () {
    expect(ImageEditorTool::RemoveBackground->creditCost())->toBe(5);
    expect(ImageEditorTool::Enhance->creditCost())->toBe(5);
    expect(ImageEditorTool::Upscale->creditCost())->toBe(10);
    expect(ImageEditorTool::EraseObject->creditCost())->toBe(10);
    expect(ImageEditorTool::Colorize->creditCost())->toBe(10);
    expect(ImageEditorTool::FaceRestore->creditCost())->toBe(10);
    expect(ImageEditorTool::Inpaint->creditCost())->toBe(15);
    expect(ImageEditorTool::StyleTransfer->creditCost())->toBe(15);
    expect(ImageEditorTool::Extend->creditCost())->toBe(15);
    expect(ImageEditorTool::CreateVariation->creditCost())->toBe(15);
});
