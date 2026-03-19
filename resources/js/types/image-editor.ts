import type { GenerationStatus } from './image-generator';

export type ImageEditorToolValue =
    | 'remove_background'
    | 'upscale'
    | 'enhance'
    | 'inpaint'
    | 'erase_object'
    | 'style_transfer'
    | 'colorize'
    | 'extend'
    | 'create_variation'
    | 'face_restore';

export type ImageEditSourceType = 'upload' | 'generated';

export type ToolDefinition = {
    value: ImageEditorToolValue;
    label: string;
    description: string;
    creditCost: number;
};

export type ImageEditStep = {
    id: number;
    step_number: number;
    tool: ImageEditorToolValue;
    tool_settings: Record<string, unknown> | null;
    credit_cost: number;
    status: GenerationStatus;
    file_url: string | null;
    error_message: string | null;
    created_at: string;
};

export type ImageEditSession = {
    id: number;
    source_type: ImageEditSourceType;
    source_url: string;
    current_step: number;
    steps: ImageEditStep[];
    created_at: string;
};
