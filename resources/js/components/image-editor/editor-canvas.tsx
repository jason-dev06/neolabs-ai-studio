import { AlertCircle, ImageIcon, Loader2, ZoomIn, ZoomOut } from 'lucide-react';
import { useCallback, useState } from 'react';

import { cn } from '@/lib/utils';
import type { ImageEditSession } from '@/types';

interface Props {
    session: ImageEditSession;
    currentImageUrl: string;
    isProcessing: boolean;
}

const ZOOM_LEVELS = [0.5, 0.75, 1, 1.25, 1.5, 2] as const;

export function EditorCanvas({
    session,
    currentImageUrl,
    isProcessing,
}: Props) {
    const [zoomIndex, setZoomIndex] = useState(2);
    const zoom = ZOOM_LEVELS[zoomIndex];

    const failedStep =
        session.steps
            .filter((s) => s.status === 'failed')
            .sort((a, b) => b.step_number - a.step_number)[0] ?? null;
    const isFailed =
        !isProcessing &&
        failedStep != null &&
        failedStep.step_number > session.current_step;

    const zoomIn = useCallback(() => {
        setZoomIndex((i) => Math.min(i + 1, ZOOM_LEVELS.length - 1));
    }, []);

    const zoomOut = useCallback(() => {
        setZoomIndex((i) => Math.max(i - 1, 0));
    }, []);

    return (
        <div className="flex h-full flex-col bg-background">
            {/* Canvas header */}
            <div className="flex items-center justify-between border-b border-border/40 px-5 py-2">
                <div className="flex items-center gap-2">
                    <div className="size-1.5 rounded-full bg-amber-accent shadow-sm shadow-amber-accent/40" />
                    <span className="text-[10px] font-bold tracking-[0.2em] text-muted-foreground/50 uppercase">
                        Canvas
                    </span>
                </div>

                {/* Zoom controls */}
                <div className="flex items-center gap-1">
                    <button
                        type="button"
                        onClick={zoomOut}
                        disabled={zoomIndex === 0}
                        className="flex size-7 items-center justify-center rounded-lg text-muted-foreground/60 transition-colors hover:bg-muted hover:text-foreground disabled:opacity-30"
                    >
                        <ZoomOut className="size-3.5" />
                    </button>
                    <span className="w-12 text-center text-[11px] font-semibold text-muted-foreground/60 tabular-nums">
                        {Math.round(zoom * 100)}%
                    </span>
                    <button
                        type="button"
                        onClick={zoomIn}
                        disabled={zoomIndex === ZOOM_LEVELS.length - 1}
                        className="flex size-7 items-center justify-center rounded-lg text-muted-foreground/60 transition-colors hover:bg-muted hover:text-foreground disabled:opacity-30"
                    >
                        <ZoomIn className="size-3.5" />
                    </button>
                </div>
            </div>

            {/* Canvas body */}
            <div className="relative flex flex-1 items-center justify-center overflow-hidden">
                {/* Dot grid background */}
                <div
                    className="absolute inset-0 opacity-[0.35] dark:opacity-[0.15]"
                    style={{
                        backgroundImage:
                            'radial-gradient(circle, oklch(0.65 0.01 65 / 0.3) 1px, transparent 1px)',
                        backgroundSize: '24px 24px',
                    }}
                />

                {/* Subtle vignette */}
                <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_center,_transparent_50%,_var(--background)_100%)]" />

                {/* Image display */}
                <div
                    className="relative z-10 flex max-h-full max-w-full items-center justify-center p-10 transition-transform duration-300 ease-out"
                    style={{ transform: `scale(${zoom})` }}
                >
                    {currentImageUrl ? (
                        <div className="group relative">
                            {/* Ambient glow behind image */}
                            <div className="absolute -inset-4 rounded-3xl bg-amber-accent/5 opacity-0 blur-2xl transition-opacity duration-500 group-hover:opacity-100" />

                            <img
                                src={currentImageUrl}
                                alt="Editing canvas"
                                className={cn(
                                    'relative max-h-[calc(100vh-200px)] max-w-full rounded-xl object-contain',
                                    'shadow-[0_8px_40px_-8px_rgba(0,0,0,0.25),0_0_0_1px_rgba(0,0,0,0.05)]',
                                    'dark:shadow-[0_8px_40px_-8px_rgba(0,0,0,0.6),0_0_0_1px_rgba(255,255,255,0.05)]',
                                    'transition-[filter] duration-500',
                                    isProcessing &&
                                        'brightness-[0.7] saturate-50',
                                )}
                            />
                        </div>
                    ) : (
                        <div className="flex flex-col items-center gap-5 text-center">
                            <div className="flex size-20 items-center justify-center rounded-2xl border border-dashed border-border/60 bg-muted/50">
                                <ImageIcon className="size-8 text-muted-foreground/30" />
                            </div>
                            <div>
                                <p className="text-sm font-medium text-muted-foreground/60">
                                    No image loaded
                                </p>
                                <p className="mt-1 text-xs text-muted-foreground/40">
                                    Select a tool and apply it to get started
                                </p>
                            </div>
                        </div>
                    )}

                    {/* Processing overlay */}
                    {isProcessing && (
                        <div className="absolute inset-0 flex flex-col items-center justify-center gap-5 rounded-xl">
                            {/* Animated concentric rings */}
                            <div className="relative flex size-20 items-center justify-center">
                                <div
                                    className="absolute size-20 animate-ping rounded-full border border-amber-accent/20"
                                    style={{ animationDuration: '2s' }}
                                />
                                <div
                                    className="absolute size-16 animate-ping rounded-full border border-amber-accent/30"
                                    style={{
                                        animationDuration: '2s',
                                        animationDelay: '0.3s',
                                    }}
                                />
                                <div
                                    className="absolute size-12 animate-ping rounded-full border border-amber-accent/40"
                                    style={{
                                        animationDuration: '2s',
                                        animationDelay: '0.6s',
                                    }}
                                />
                                <div className="relative flex size-12 items-center justify-center rounded-full bg-gradient-to-br from-amber-accent to-amber-accent-dark shadow-lg shadow-amber-accent/30">
                                    <Loader2 className="size-5 animate-spin text-white" />
                                </div>
                            </div>
                            <div className="text-center">
                                <p className="text-sm font-semibold tracking-tight text-foreground">
                                    Applying AI tool...
                                </p>
                                <p className="mt-1.5 text-xs text-muted-foreground">
                                    This may take a few seconds
                                </p>
                            </div>
                        </div>
                    )}

                    {/* Failed overlay */}
                    {isFailed && (
                        <div className="absolute inset-0 flex flex-col items-center justify-center gap-4 rounded-xl bg-black/40 backdrop-blur-sm">
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
        </div>
    );
}
