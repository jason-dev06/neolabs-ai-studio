import { router } from '@inertiajs/react';
import {
    Captions,
    ChevronRight,
    Film,
    Gauge,
    Scissors,
    Sparkles,
    Trash2,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { useState } from 'react';

import { Button } from '@/components/ui/button';
import { ToolSettingsPanel } from '@/components/video-editor/tool-settings-panel';
import { cn } from '@/lib/utils';
import { destroy } from '@/routes/video-editor';
import type {
    VideoEditorToolValue,
    VideoEditSession,
    VideoToolDefinition,
} from '@/types';

const TOOL_ICONS: Record<VideoEditorToolValue, LucideIcon> = {
    trim_cut: Scissors,
    speed_control: Gauge,
    auto_captions: Captions,
    ai_effects: Sparkles,
    extend_video: Film,
};

const TOOL_COLORS: Record<VideoEditorToolValue, string> = {
    trim_cut: 'from-rose-500/80 to-rose-600/80',
    speed_control: 'from-sky-500/80 to-sky-600/80',
    auto_captions: 'from-violet-500/80 to-violet-600/80',
    ai_effects: 'from-amber-accent to-amber-accent-dark',
    extend_video: 'from-emerald-500/80 to-emerald-600/80',
};

interface Props {
    session: VideoEditSession;
    tools: VideoToolDefinition[];
    credits: number;
    isProcessing: boolean;
}

export function ToolSidebar({ session, tools, credits, isProcessing }: Props) {
    const defaultTool = tools[0] ?? null;
    const [selectedTool, setSelectedTool] =
        useState<VideoToolDefinition | null>(defaultTool);

    function handleClose() {
        router.delete(destroy.url({ session: session.id }));
    }

    return (
        <div className="flex h-full flex-col overflow-hidden border-r border-border/40 bg-card/80 backdrop-blur-sm">
            {/* Source video header */}
            <div className="border-b border-border/40 p-4">
                <div className="flex items-center gap-3">
                    <div className="relative size-12 shrink-0 overflow-hidden rounded-lg border border-border/60 shadow-sm">
                        <video
                            src={session.source_url}
                            className="size-full object-cover"
                            muted
                        />
                        {/* Tiny play triangle overlay */}
                        <div className="absolute inset-0 flex items-center justify-center bg-black/20">
                            <div className="size-0 border-y-[5px] border-l-[8px] border-y-transparent border-l-white/80" />
                        </div>
                    </div>
                    <div className="min-w-0 flex-1">
                        <p className="text-[10px] font-bold tracking-[0.15em] text-muted-foreground/50 uppercase">
                            Source
                        </p>
                        <p className="mt-0.5 truncate text-xs text-muted-foreground">
                            {session.source_type === 'generated'
                                ? 'From Video Generator'
                                : 'Uploaded video'}
                        </p>
                    </div>
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        className="size-7 shrink-0 rounded-lg text-muted-foreground/30 hover:text-destructive-foreground"
                        onClick={handleClose}
                        title="Delete session"
                    >
                        <Trash2 className="size-3.5" />
                    </Button>
                </div>
            </div>

            {/* Scrollable content */}
            <div className="flex-1 overflow-y-auto">
                {/* AI Tools list */}
                <div className="p-3">
                    <div className="mb-2 flex items-center gap-2 px-1">
                        <Sparkles className="size-3 text-amber-accent" />
                        <span className="text-[10px] font-bold tracking-[0.15em] text-muted-foreground/50 uppercase">
                            AI Tools
                        </span>
                    </div>

                    <div className="space-y-1">
                        {tools.map((tool) => {
                            const Icon = TOOL_ICONS[tool.value];
                            const isSelected =
                                selectedTool?.value === tool.value;
                            const gradientClass = TOOL_COLORS[tool.value];

                            return (
                                <button
                                    key={tool.value}
                                    type="button"
                                    onClick={() => setSelectedTool(tool)}
                                    className={cn(
                                        'group relative flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left transition-all duration-200',
                                        isSelected
                                            ? 'bg-muted/80 dark:bg-muted/60'
                                            : 'hover:bg-muted/50',
                                    )}
                                >
                                    {/* Active indicator line */}
                                    {isSelected && (
                                        <div
                                            className={cn(
                                                'absolute top-2 bottom-2 left-0 w-[3px] rounded-r-full bg-gradient-to-b',
                                                gradientClass,
                                            )}
                                        />
                                    )}

                                    <div
                                        className={cn(
                                            'flex size-8 shrink-0 items-center justify-center rounded-lg transition-all duration-200',
                                            isSelected
                                                ? `bg-gradient-to-br ${gradientClass} text-white shadow-sm`
                                                : 'bg-muted text-muted-foreground/60 group-hover:bg-background group-hover:text-foreground/80',
                                        )}
                                    >
                                        <Icon className="size-3.5" />
                                    </div>
                                    <div className="min-w-0 flex-1">
                                        <p
                                            className={cn(
                                                'truncate text-[13px] leading-none font-medium',
                                                isSelected
                                                    ? 'text-foreground'
                                                    : 'text-foreground/80',
                                            )}
                                        >
                                            {tool.label}
                                        </p>
                                        <p className="mt-1 truncate text-[11px] leading-none text-muted-foreground/60">
                                            {tool.description}
                                        </p>
                                    </div>
                                    <div className="flex shrink-0 items-center gap-1">
                                        <span
                                            className={cn(
                                                'rounded-md px-1.5 py-0.5 text-[10px] font-bold tabular-nums',
                                                isSelected
                                                    ? 'bg-amber-accent/10 text-amber-accent'
                                                    : 'text-muted-foreground/35',
                                            )}
                                        >
                                            {tool.creditCost}cr
                                        </span>
                                        {isSelected && (
                                            <ChevronRight className="size-3 text-muted-foreground/40" />
                                        )}
                                    </div>
                                </button>
                            );
                        })}
                    </div>
                </div>

                {/* Tool settings */}
                {selectedTool && (
                    <div className="border-t border-border/40 p-4">
                        <ToolSettingsPanel
                            sessionId={session.id}
                            tool={selectedTool}
                            credits={credits}
                            isProcessing={isProcessing}
                        />
                    </div>
                )}
            </div>
        </div>
    );
}
