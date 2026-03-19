import { AlertCircle, Loader2, Maximize2, Video, Volume2 } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';

import { cn } from '@/lib/utils';
import type { VideoEditSession } from '@/types';

interface Props {
    session: VideoEditSession;
    currentVideoUrl: string;
    isProcessing: boolean;
}

export function EditorCanvas({
    session,
    currentVideoUrl,
    isProcessing,
}: Props) {
    const videoRef = useRef<HTMLVideoElement>(null);
    const [isPlaying, setIsPlaying] = useState(false);
    const [currentTime, setCurrentTime] = useState(0);
    const [duration, setDuration] = useState(0);

    const failedStep =
        session.steps
            .filter((s) => s.status === 'failed')
            .sort((a, b) => b.step_number - a.step_number)[0] ?? null;
    const isFailed =
        !isProcessing &&
        failedStep != null &&
        failedStep.step_number > session.current_step;

    useEffect(() => {
        const video = videoRef.current;

        if (!video) {
            return;
        }

        const onTimeUpdate = () => setCurrentTime(video.currentTime);
        const onDurationChange = () => setDuration(video.duration || 0);
        const onPlay = () => setIsPlaying(true);
        const onPause = () => setIsPlaying(false);

        video.addEventListener('timeupdate', onTimeUpdate);
        video.addEventListener('durationchange', onDurationChange);
        video.addEventListener('play', onPlay);
        video.addEventListener('pause', onPause);

        return () => {
            video.removeEventListener('timeupdate', onTimeUpdate);
            video.removeEventListener('durationchange', onDurationChange);
            video.removeEventListener('play', onPlay);
            video.removeEventListener('pause', onPause);
        };
    }, [currentVideoUrl]);

    const toggleFullscreen = useCallback(() => {
        const video = videoRef.current;

        if (video) {
            if (document.fullscreenElement) {
                document.exitFullscreen();
            } else {
                video.requestFullscreen();
            }
        }
    }, []);

    function formatTime(seconds: number): string {
        if (!Number.isFinite(seconds)) {
            return '0:00';
        }

        const m = Math.floor(seconds / 60);
        const s = Math.floor(seconds % 60);

        return `${m}:${s.toString().padStart(2, '0')}`;
    }

    const progress = duration > 0 ? (currentTime / duration) * 100 : 0;

    return (
        <div className="relative flex h-full flex-col">
            {/* Cinema canvas */}
            <div className="relative flex flex-1 items-center justify-center overflow-hidden bg-warm-900/40 dark:bg-black/30">
                {/* Subtle grid pattern */}
                <div
                    className="absolute inset-0 opacity-[0.04] dark:opacity-[0.06]"
                    style={{
                        backgroundImage: `
                            linear-gradient(to right, oklch(0.65 0.01 65 / 0.5) 1px, transparent 1px),
                            linear-gradient(to bottom, oklch(0.65 0.01 65 / 0.5) 1px, transparent 1px)
                        `,
                        backgroundSize: '40px 40px',
                    }}
                />

                {/* Corner markers — editing bay aesthetic */}
                <div className="pointer-events-none absolute top-6 left-6 size-8 border-t-2 border-l-2 border-amber-accent/15 dark:border-amber-accent/10" />
                <div className="pointer-events-none absolute top-6 right-6 size-8 border-t-2 border-r-2 border-amber-accent/15 dark:border-amber-accent/10" />
                <div className="pointer-events-none absolute bottom-6 left-6 size-8 border-b-2 border-l-2 border-amber-accent/15 dark:border-amber-accent/10" />
                <div className="pointer-events-none absolute right-6 bottom-6 size-8 border-r-2 border-b-2 border-amber-accent/15 dark:border-amber-accent/10" />

                {/* Vignette */}
                <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_center,_transparent_40%,_var(--background)_110%)] opacity-60" />

                {/* Video display */}
                <div className="relative z-10 flex max-h-full max-w-full items-center justify-center p-8">
                    {currentVideoUrl ? (
                        <div className="group relative">
                            {/* Ambient glow behind video */}
                            <div
                                className={cn(
                                    'absolute -inset-6 rounded-2xl blur-3xl transition-opacity duration-700',
                                    'bg-amber-accent/4 dark:bg-amber-accent/6',
                                    isPlaying
                                        ? 'opacity-100'
                                        : 'opacity-0 group-hover:opacity-60',
                                )}
                            />

                            {/* Video frame */}
                            <div className="relative overflow-hidden rounded-lg shadow-[0_20px_80px_-20px_rgba(0,0,0,0.4)] dark:shadow-[0_20px_80px_-20px_rgba(0,0,0,0.7)]">
                                <video
                                    ref={videoRef}
                                    src={currentVideoUrl}
                                    controls
                                    className={cn(
                                        'relative max-h-[calc(100vh-280px)] max-w-full object-contain',
                                        'transition-[filter] duration-500',
                                        isProcessing &&
                                            'brightness-[0.6] saturate-[0.3]',
                                    )}
                                />

                                {/* Floating toolbar on hover */}
                                <div className="absolute right-3 bottom-14 flex translate-y-2 gap-1.5 opacity-0 transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100">
                                    <button
                                        type="button"
                                        onClick={toggleFullscreen}
                                        className="flex size-8 items-center justify-center rounded-md bg-black/60 text-white/80 backdrop-blur-sm transition-colors hover:bg-black/80 hover:text-white"
                                    >
                                        <Maximize2 className="size-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    ) : (
                        <div className="flex flex-col items-center gap-6 text-center">
                            <div className="relative">
                                <div className="absolute -inset-4 rounded-3xl bg-amber-accent/5 blur-xl" />
                                <div className="relative flex size-20 items-center justify-center rounded-2xl border border-dashed border-amber-accent/20 bg-card/50">
                                    <Video className="size-8 text-muted-foreground/30" />
                                </div>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-muted-foreground/60">
                                    No video loaded
                                </p>
                                <p className="mt-1 text-xs text-muted-foreground/40">
                                    Select a tool and apply it to get started
                                </p>
                            </div>
                        </div>
                    )}

                    {/* Processing overlay */}
                    {isProcessing && (
                        <div className="absolute inset-0 flex flex-col items-center justify-center gap-5 rounded-lg">
                            {/* Orbital rings */}
                            <div className="relative flex size-24 items-center justify-center">
                                <div
                                    className="absolute inset-0 rounded-full border border-amber-accent/20"
                                    style={{
                                        animation: 'spin 4s linear infinite',
                                    }}
                                />
                                <div
                                    className="absolute inset-2 rounded-full border border-dashed border-amber-accent/15"
                                    style={{
                                        animation:
                                            'spin 6s linear infinite reverse',
                                    }}
                                />
                                <div
                                    className="absolute inset-4 rounded-full border border-amber-accent/25"
                                    style={{
                                        animation: 'spin 3s linear infinite',
                                    }}
                                />
                                <div className="relative flex size-12 items-center justify-center rounded-full bg-gradient-to-br from-amber-accent to-amber-accent-dark shadow-lg shadow-amber-accent/25">
                                    <Loader2 className="size-5 animate-spin text-white" />
                                </div>
                            </div>
                            <div className="text-center">
                                <p className="text-sm font-semibold tracking-tight text-foreground">
                                    Processing your edit...
                                </p>
                                <p className="mt-1.5 text-xs text-muted-foreground">
                                    This may take up to a minute
                                </p>
                            </div>
                        </div>
                    )}

                    {/* Failed overlay */}
                    {isFailed && (
                        <div className="absolute inset-0 flex flex-col items-center justify-center gap-4 rounded-lg bg-black/50 backdrop-blur-sm">
                            <div className="flex size-14 items-center justify-center rounded-full bg-gradient-to-br from-red-500 to-red-700 shadow-lg shadow-red-500/30">
                                <AlertCircle className="size-6 text-white" />
                            </div>
                            <div className="text-center">
                                <p className="text-sm font-semibold text-white">
                                    Processing failed
                                </p>
                                <p className="mt-1.5 max-w-xs text-xs leading-relaxed text-white/60">
                                    {failedStep?.error_message ??
                                        'Something went wrong. Please try again.'}
                                </p>
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Mini progress bar under video area */}
            {currentVideoUrl && duration > 0 && (
                <div className="flex items-center gap-3 border-t border-border/30 bg-card/50 px-5 py-1.5">
                    <Volume2 className="size-3 text-muted-foreground/40" />
                    <div className="relative h-1 flex-1 overflow-hidden rounded-full bg-border/40">
                        <div
                            className="absolute inset-y-0 left-0 rounded-full bg-amber-accent/60 transition-all duration-200"
                            style={{ width: `${progress}%` }}
                        />
                    </div>
                    <span className="text-[10px] font-medium text-muted-foreground/50 tabular-nums">
                        {formatTime(currentTime)} / {formatTime(duration)}
                    </span>
                </div>
            )}
        </div>
    );
}
