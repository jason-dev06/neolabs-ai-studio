import { router } from '@inertiajs/react';
import {
    ArrowUpRight,
    Copy,
    Droplets,
    Eraser,
    Lightbulb,
    Maximize2,
    Paintbrush,
    Palette,
    Scissors,
    SmilePlus,
    Sparkles,
    Trash2,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { useState } from 'react';

import { ToolSettingsPanel } from '@/components/image-editor/tool-settings-panel';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { destroy } from '@/routes/image-editor';
import type {
    ImageEditorToolValue,
    ImageEditSession,
    ToolDefinition,
} from '@/types';

const TOOL_ICONS: Record<ImageEditorToolValue, LucideIcon> = {
    remove_background: Scissors,
    upscale: ArrowUpRight,
    enhance: Sparkles,
    inpaint: Paintbrush,
    erase_object: Eraser,
    style_transfer: Palette,
    colorize: Droplets,
    extend: Maximize2,
    create_variation: Copy,
    face_restore: SmilePlus,
};

const EDITOR_TIPS = [
    'Start with Remove Background for cleaner compositions.',
    'Upscale before Enhance for best quality results.',
    'Use Inpaint to fill in or replace specific areas.',
];

interface Props {
    session: ImageEditSession;
    tools: ToolDefinition[];
    credits: number;
    isProcessing: boolean;
}

export function ToolSidebar({ session, tools, credits, isProcessing }: Props) {
    const defaultTool = tools[0] ?? null;
    const [selectedTool, setSelectedTool] = useState<ToolDefinition | null>(
        defaultTool,
    );

    function handleClose() {
        router.delete(destroy.url({ session: session.id }));
    }

    return (
        <div className="flex h-full flex-col overflow-hidden border-r border-border/40 bg-card">
            {/* Source image header */}
            <div className="border-b border-border/40 p-4">
                <div className="flex items-center gap-3">
                    <div className="relative size-14 shrink-0 overflow-hidden rounded-xl border border-border/60 shadow-sm">
                        <img
                            src={session.source_url}
                            alt="Source"
                            className="size-full object-cover"
                        />
                    </div>
                    <div className="min-w-0 flex-1">
                        <p className="text-[10px] font-bold tracking-[0.15em] text-muted-foreground/50 uppercase">
                            Source
                        </p>
                        <p className="mt-0.5 truncate text-xs text-muted-foreground">
                            {session.source_type === 'generated'
                                ? 'From Image Generator'
                                : 'Uploaded image'}
                        </p>
                    </div>
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        className="size-7 shrink-0 rounded-lg text-muted-foreground/40 hover:text-destructive-foreground"
                        onClick={handleClose}
                        title="Delete session"
                    >
                        <Trash2 className="size-3.5" />
                    </Button>
                </div>
            </div>

            {/* AI Tools list */}
            <div className="flex-1 overflow-y-auto">
                <div className="p-4">
                    <div className="mb-3 flex items-center gap-2">
                        <Sparkles className="size-3 text-amber-accent" />
                        <span className="text-[10px] font-bold tracking-[0.15em] text-muted-foreground/50 uppercase">
                            AI Tools
                        </span>
                    </div>

                    <div className="space-y-0.5">
                        {tools.map((tool) => {
                            const Icon = TOOL_ICONS[tool.value];
                            const isSelected =
                                selectedTool?.value === tool.value;

                            return (
                                <button
                                    key={tool.value}
                                    type="button"
                                    onClick={() => setSelectedTool(tool)}
                                    className={cn(
                                        'group flex w-full items-center gap-2.5 rounded-xl px-2.5 py-2 text-left transition-all duration-200',
                                        isSelected
                                            ? 'bg-gradient-to-r from-amber-accent to-amber-accent-dark text-white shadow-md shadow-amber-accent/15'
                                            : 'text-foreground hover:bg-muted/80',
                                    )}
                                >
                                    <div
                                        className={cn(
                                            'flex size-7 shrink-0 items-center justify-center rounded-lg transition-all duration-200',
                                            isSelected
                                                ? 'bg-white/20 shadow-inner shadow-white/10'
                                                : 'bg-muted group-hover:bg-background',
                                        )}
                                    >
                                        <Icon className="size-3.5" />
                                    </div>
                                    <div className="min-w-0 flex-1">
                                        <p className="truncate text-[13px] leading-none font-medium">
                                            {tool.label}
                                        </p>
                                        <p
                                            className={cn(
                                                'mt-1 truncate text-[11px] leading-none',
                                                isSelected
                                                    ? 'text-white/60'
                                                    : 'text-muted-foreground/70',
                                            )}
                                        >
                                            {tool.description}
                                        </p>
                                    </div>
                                    <span
                                        className={cn(
                                            'shrink-0 rounded-md px-1.5 py-0.5 text-[10px] font-bold tabular-nums',
                                            isSelected
                                                ? 'bg-white/15 text-white/80'
                                                : 'text-muted-foreground/40',
                                        )}
                                    >
                                        {tool.creditCost}cr
                                    </span>
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

                {/* Tips */}
                <div className="border-t border-border/40 p-4">
                    <div className="flex items-center gap-2">
                        <Lightbulb className="size-3 text-amber-accent/60" />
                        <span className="text-[10px] font-bold tracking-[0.15em] text-muted-foreground/40 uppercase">
                            Tips
                        </span>
                    </div>
                    <ul className="mt-2.5 space-y-2">
                        {EDITOR_TIPS.map((tip, i) => (
                            <li
                                key={i}
                                className="flex gap-2 text-[11px] leading-relaxed text-muted-foreground/60"
                            >
                                <span className="mt-[7px] block size-1 shrink-0 rounded-full bg-amber-accent/30" />
                                {tip}
                            </li>
                        ))}
                    </ul>
                </div>
            </div>
        </div>
    );
}
