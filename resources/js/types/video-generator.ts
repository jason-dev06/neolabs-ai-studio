import type { GenerationStatus } from './image-generator';

export type VideoQualityTier = 'fast' | 'standard';
export type VideoDuration = '4' | '6' | '8';
export type VideoStyle =
    | 'cinematic'
    | 'anime'
    | 'documentary'
    | 'commercial'
    | 'music_video'
    | 'vlog';
export type VideoAspectRatio = '16:9' | '9:16';

export type GeneratedVideo = {
    id: number;
    prompt: string;
    quality_tier: VideoQualityTier;
    duration: VideoDuration;
    aspect_ratio: VideoAspectRatio;
    video_style: VideoStyle;
    credit_cost: number;
    status: GenerationStatus;
    file_url: string | null;
    thumbnail_url: string | null;
    batch_id: string;
    created_at: string;
};
