export type QualityTier = 'basic' | 'smart' | 'genius';
export type AspectRatio = '1:1' | '16:9' | '9:16' | '4:3' | '3:4';
export type GenerationStatus =
    | 'pending'
    | 'processing'
    | 'completed'
    | 'failed';

export type GeneratedImage = {
    id: number;
    prompt: string;
    quality_tier: QualityTier;
    aspect_ratio: AspectRatio;
    credit_cost: number;
    status: GenerationStatus;
    file_url: string | null;
    batch_id: string;
    created_at: string;
};
