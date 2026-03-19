import type { GenerationStatus } from './image-generator';

export type VideoEditorToolValue =
    | 'trim_cut'
    | 'speed_control'
    | 'auto_captions'
    | 'ai_effects'
    | 'extend_video';

export type VideoEditSourceType = 'upload' | 'generated';

export type VideoToolDefinition = {
    value: VideoEditorToolValue;
    label: string;
    description: string;
    creditCost: number;
};

export type VideoEditStep = {
    id: number;
    step_number: number;
    tool: VideoEditorToolValue;
    tool_settings: Record<string, unknown> | null;
    credit_cost: number;
    status: GenerationStatus;
    file_url: string | null;
    error_message: string | null;
    created_at: string;
};

export type VideoEditSession = {
    id: number;
    source_type: VideoEditSourceType;
    source_url: string;
    current_step: number;
    steps: VideoEditStep[];
    created_at: string;
};
