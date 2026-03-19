import {
    Captions,
    Check,
    Clock,
    Film,
    Gauge,
    Loader2,
    Scissors,
    Sparkles,
    X,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

import { cn } from '@/lib/utils';
import type { VideoEditorToolValue, VideoEditSession } from '@/types';

const TOOL_ICONS: Record<VideoEditorToolValue, LucideIcon> = {
    trim_cut: Scissors,
    speed_control: Gauge,
    auto_captions: Captions,
    ai_effects: Sparkles,
    extend_video: Film,
};

const TOOL_SHORT_LABELS: Record<VideoEditorToolValue, string> = {
    trim_cut: 'Trim',
    speed_control: 'Speed',
    auto_captions: 'Captions',
    ai_effects: 'Effects',
    extend_video: 'Extend',
};

interface Props {
    session: VideoEditSession;
    isProcessing: boolean;
}

export function EditTimeline({ session, isProcessing }: Props) {
    const allSteps = session.steps.toSorted(
        (a, b) => a.step_number - b.step_number,
    );

    if (allSteps.length === 0 && !isProcessing) {
        return (
            <div className="flex items-center justify-center border-t border-border/30 bg-card/50 px-5 py-3">
                <p className="text-[11px] text-muted-foreground/40">
                    Apply a tool to see your edit timeline here
                </p>
            </div>
        );
    }

    return (
        <div className="border-t border-border/30 bg-card/50">
            <div className="flex items-center gap-2 px-5 py-2.5">
                {/* Timeline label */}
                <div className="flex items-center gap-1.5 pr-2">
                    <Clock className="size-3 text-muted-foreground/40" />
                    <span className="text-[10px] font-bold tracking-[0.12em] text-muted-foreground/40 uppercase">
                        Timeline
                    </span>
                </div>

                {/* Origin marker */}
                <div
                    className={cn(
                        'flex items-center gap-1.5 rounded-md border px-2.5 py-1',
                        session.current_step === 0
                            ? 'border-amber-accent/30 bg-amber-accent/8 text-amber-accent'
                            : 'border-border/40 text-muted-foreground/50',
                    )}
                >
                    <div className="size-1.5 rounded-full bg-current" />
                    <span className="text-[10px] font-semibold">Original</span>
                </div>

                {/* Connector line */}
                {allSteps.length > 0 && (
                    <div className="h-px w-3 bg-border/40" />
                )}

                {/* Step items */}
                <div className="flex items-center gap-1.5 overflow-x-auto">
                    {allSteps.map((step, idx) => {
                        const Icon = TOOL_ICONS[step.tool];
                        const label = TOOL_SHORT_LABELS[step.tool];
                        const isActive =
                            step.step_number === session.current_step;
                        const isPast =
                            step.step_number < session.current_step &&
                            step.status === 'completed';
                        const isCurrent =
                            step.status === 'processing' ||
                            step.status === 'pending';
                        const isFailed = step.status === 'failed';

                        return (
                            <div key={step.id} className="flex items-center">
                                {idx > 0 && (
                                    <div
                                        className={cn(
                                            'mr-1.5 h-px w-2',
                                            isPast || isActive
                                                ? 'bg-amber-accent/30'
                                                : 'bg-border/30',
                                        )}
                                    />
                                )}
                                <div
                                    className={cn(
                                        'flex items-center gap-1.5 rounded-md border px-2.5 py-1 transition-all duration-200',
                                        isActive &&
                                            step.status === 'completed' &&
                                            'border-amber-accent/30 bg-amber-accent/8 text-amber-accent',
                                        isPast &&
                                            'border-border/30 bg-transparent text-muted-foreground/50',
                                        isCurrent &&
                                            'border-amber-accent/20 bg-amber-accent/5 text-amber-accent',
                                        isFailed &&
                                            'border-destructive/20 bg-destructive/5 text-destructive-foreground',
                                        !isActive &&
                                            !isPast &&
                                            !isCurrent &&
                                            !isFailed &&
                                            'border-border/40 text-muted-foreground/50',
                                    )}
                                >
                                    {isCurrent ? (
                                        <Loader2 className="size-3 animate-spin" />
                                    ) : isFailed ? (
                                        <X className="size-3" />
                                    ) : isPast ? (
                                        <Check className="size-3" />
                                    ) : (
                                        <Icon className="size-3" />
                                    )}
                                    <span className="text-[10px] font-semibold whitespace-nowrap">
                                        {label}
                                    </span>
                                </div>
                            </div>
                        );
                    })}
                </div>

                {/* Processing pulse */}
                {isProcessing && (
                    <div className="ml-auto flex items-center gap-1.5">
                        <div
                            className="size-1.5 rounded-full bg-amber-accent"
                            style={{
                                animation: 'pulse 1.5s ease-in-out infinite',
                            }}
                        />
                        <span className="text-[10px] font-medium text-amber-accent/70">
                            Processing
                        </span>
                    </div>
                )}
            </div>
        </div>
    );
}
