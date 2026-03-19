<?php

namespace App\Enums;

enum ImageEditorTool: string
{
    case RemoveBackground = 'remove_background';
    case Upscale = 'upscale';
    case Enhance = 'enhance';
    case Inpaint = 'inpaint';
    case EraseObject = 'erase_object';
    case StyleTransfer = 'style_transfer';
    case Colorize = 'colorize';
    case Extend = 'extend';
    case CreateVariation = 'create_variation';
    case FaceRestore = 'face_restore';

    public function creditCost(): int
    {
        return match ($this) {
            self::RemoveBackground => 5,
            self::Enhance => 5,
            self::Upscale => 10,
            self::EraseObject => 10,
            self::Colorize => 10,
            self::FaceRestore => 10,
            self::Inpaint => 15,
            self::StyleTransfer => 15,
            self::Extend => 15,
            self::CreateVariation => 15,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::RemoveBackground => 'Remove Background',
            self::Upscale => 'Upscale',
            self::Enhance => 'Enhance',
            self::Inpaint => 'Inpaint / Edit',
            self::EraseObject => 'Erase Object',
            self::StyleTransfer => 'Style Transfer',
            self::Colorize => 'Colorize',
            self::Extend => 'Extend / Outpaint',
            self::CreateVariation => 'Create Variation',
            self::FaceRestore => 'Face Restore',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::RemoveBackground => 'Remove background to transparent',
            self::Upscale => 'Enhance resolution & quality',
            self::Enhance => 'Improve overall quality',
            self::Inpaint => 'Add or modify parts',
            self::EraseObject => 'Remove unwanted objects',
            self::StyleTransfer => 'Apply artistic styles',
            self::Colorize => 'Add color to B&W images',
            self::Extend => 'Expand image boundaries',
            self::CreateVariation => 'Generate a new variant',
            self::FaceRestore => 'Enhance and restore faces',
        };
    }

    public function prompt(array $settings = []): string
    {
        return match ($this) {
            self::RemoveBackground => 'Remove the background from this image, making it transparent. Keep only the main subject.',
            self::Upscale => sprintf(
                'Upscale this image to %dx resolution while preserving details and sharpness.',
                $settings['scale_factor'] ?? 2
            ),
            self::Enhance => 'Enhance the overall quality of this image. Improve clarity, color balance, and sharpness while maintaining the original composition.',
            self::Inpaint => sprintf(
                'Edit this image: %s',
                $settings['prompt'] ?? 'Improve this image'
            ),
            self::EraseObject => sprintf(
                'Remove the following from this image and fill in naturally: %s',
                $settings['erase_prompt'] ?? 'Remove unwanted objects'
            ),
            self::StyleTransfer => sprintf(
                'Apply a %s artistic style to this image while preserving the main subject and composition.',
                $settings['style'] ?? 'painterly'
            ),
            self::Colorize => 'Add natural, realistic color to this black and white image. Maintain the original composition and lighting.',
            self::Extend => sprintf(
                'Extend this image %s, seamlessly generating new content that matches the existing style and composition.',
                $settings['direction'] ?? 'in all directions'
            ),
            self::CreateVariation => 'Create a new variation of this image. Maintain the overall theme and composition but introduce creative differences.',
            self::FaceRestore => 'Restore and enhance the faces in this image. Improve clarity, fix artifacts, and enhance facial details while maintaining natural appearance.',
        };
    }
}
