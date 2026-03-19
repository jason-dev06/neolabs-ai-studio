import { router } from '@inertiajs/react';
import { Coins, Loader2, Zap } from 'lucide-react';
import { useState } from 'react';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { cn } from '@/lib/utils';
import { applyTool } from '@/routes/video-editor';
import type { VideoEditorToolValue, VideoToolDefinition } from '@/types';

interface Props {
    sessionId: number;
    tool: VideoToolDefinition;
    credits: number;
    isProcessing: boolean;
}

const SPEED_OPTIONS = [
    { value: '0.25', label: '0.25x' },
    { value: '0.5', label: '0.5x' },
    { value: '1.5', label: '1.5x' },
    { value: '2', label: '2x' },
    { value: '4', label: '4x' },
];

const LANGUAGE_OPTIONS = [
    { value: 'en', label: 'English' },
    { value: 'es', label: 'Spanish' },
    { value: 'fr', label: 'French' },
    { value: 'de', label: 'German' },
    { value: 'pt', label: 'Portuguese' },
    { value: 'ja', label: 'Japanese' },
    { value: 'zh', label: 'Chinese' },
];

const EFFECT_OPTIONS = [
    { value: 'cinematic', label: 'Cinematic' },
    { value: 'vintage', label: 'Vintage' },
    { value: 'glitch', label: 'Glitch' },
    { value: 'neon', label: 'Neon' },
    { value: 'blur_bg', label: 'Blur BG' },
    { value: 'color_grade', label: 'Color Grade' },
    { value: 'slow_zoom', label: 'Slow Zoom' },
    { value: 'film_grain', label: 'Film Grain' },
];

const EXTEND_DURATION_OPTIONS = [
    { value: '2', label: '2s' },
    { value: '4', label: '4s' },
    { value: '6', label: '6s' },
];

function getDefaultSettings(
    tool: VideoEditorToolValue,
): Record<string, string> {
    switch (tool) {
        case 'trim_cut':
            return { start_time: '00:00', end_time: '00:10' };
        case 'speed_control':
            return { speed_factor: '1.5' };
        case 'auto_captions':
            return { language: 'en' };
        case 'ai_effects':
            return { effect: 'cinematic' };
        case 'extend_video':
            return { extend_duration: '4', prompt: '' };
        default:
            return {};
    }
}

export function ToolSettingsPanel({
    sessionId,
    tool,
    credits,
    isProcessing,
}: Props) {
    const [settings, setSettings] = useState<Record<string, string>>(
        getDefaultSettings(tool.value),
    );
    const [applying, setApplying] = useState(false);

    const canAfford = credits >= tool.creditCost;

    function updateSetting(key: string, value: string) {
        setSettings((prev) => ({ ...prev, [key]: value }));
    }

    function handleApply() {
        setApplying(true);
        router.post(
            applyTool.url({ session: sessionId }),
            {
                tool: tool.value,
                tool_settings: settings,
            },
            {
                preserveScroll: true,
                onFinish: () => setApplying(false),
            },
        );
    }

    const toolLabel = tool.label;
    const isBusy = applying || isProcessing;

    return (
        <div className="space-y-4">
            <div className="flex items-center justify-between">
                <span className="text-[10px] font-bold tracking-[0.15em] text-muted-foreground/50 uppercase">
                    {toolLabel} Settings
                </span>
                <div
                    className={cn(
                        'flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold',
                        canAfford
                            ? 'bg-amber-accent/10 text-amber-accent'
                            : 'bg-destructive/10 text-destructive-foreground',
                    )}
                >
                    <Coins className="size-2.5" />
                    <span>{tool.creditCost} credits</span>
                </div>
            </div>

            {tool.value === 'trim_cut' && (
                <div className="grid grid-cols-2 gap-3">
                    <div className="space-y-1.5">
                        <Label className="text-[11px] text-muted-foreground/70">
                            Start
                        </Label>
                        <Input
                            placeholder="00:00"
                            value={settings.start_time ?? '00:00'}
                            onChange={(e) =>
                                updateSetting('start_time', e.target.value)
                            }
                            className="h-9 rounded-lg border-border/60 bg-background text-sm font-medium tabular-nums placeholder:text-muted-foreground/40"
                        />
                    </div>
                    <div className="space-y-1.5">
                        <Label className="text-[11px] text-muted-foreground/70">
                            End
                        </Label>
                        <Input
                            placeholder="00:10"
                            value={settings.end_time ?? '00:10'}
                            onChange={(e) =>
                                updateSetting('end_time', e.target.value)
                            }
                            className="h-9 rounded-lg border-border/60 bg-background text-sm font-medium tabular-nums placeholder:text-muted-foreground/40"
                        />
                    </div>
                </div>
            )}

            {tool.value === 'speed_control' && (
                <div className="space-y-1.5">
                    <Label className="text-[11px] text-muted-foreground/70">
                        Speed Factor
                    </Label>
                    <ToggleGroup
                        type="single"
                        variant="outline"
                        value={settings.speed_factor ?? '1.5'}
                        onValueChange={(v) => {
                            if (v) {
                                updateSetting('speed_factor', v);
                            }
                        }}
                        className="justify-start gap-1.5 shadow-none"
                    >
                        {SPEED_OPTIONS.map((opt) => (
                            <ToggleGroupItem
                                key={opt.value}
                                value={opt.value}
                                className="!rounded-lg !border !border-border/60 px-3 py-1.5 text-xs font-medium shadow-none"
                            >
                                {opt.label}
                            </ToggleGroupItem>
                        ))}
                    </ToggleGroup>
                </div>
            )}

            {tool.value === 'auto_captions' && (
                <div className="space-y-1.5">
                    <Label className="text-[11px] text-muted-foreground/70">
                        Language
                    </Label>
                    <ToggleGroup
                        type="single"
                        variant="outline"
                        value={settings.language ?? 'en'}
                        onValueChange={(v) => {
                            if (v) {
                                updateSetting('language', v);
                            }
                        }}
                        className="flex-wrap justify-start gap-1.5 shadow-none"
                    >
                        {LANGUAGE_OPTIONS.map((opt) => (
                            <ToggleGroupItem
                                key={opt.value}
                                value={opt.value}
                                className="!rounded-lg !border !border-border/60 px-2.5 py-1.5 text-xs font-medium shadow-none"
                            >
                                {opt.label}
                            </ToggleGroupItem>
                        ))}
                    </ToggleGroup>
                </div>
            )}

            {tool.value === 'ai_effects' && (
                <div className="space-y-1.5">
                    <Label className="text-[11px] text-muted-foreground/70">
                        Effect
                    </Label>
                    <div className="grid grid-cols-2 gap-1.5">
                        {EFFECT_OPTIONS.map((opt) => (
                            <button
                                key={opt.value}
                                type="button"
                                onClick={() =>
                                    updateSetting('effect', opt.value)
                                }
                                className={cn(
                                    'rounded-lg border px-3 py-2 text-xs font-medium transition-all duration-150',
                                    settings.effect === opt.value
                                        ? 'border-amber-accent/40 bg-amber-accent/10 text-amber-accent shadow-sm shadow-amber-accent/10'
                                        : 'border-border/60 text-muted-foreground hover:border-border hover:text-foreground',
                                )}
                            >
                                {opt.label}
                            </button>
                        ))}
                    </div>
                </div>
            )}

            {tool.value === 'extend_video' && (
                <div className="space-y-3">
                    <div className="space-y-1.5">
                        <Label className="text-[11px] text-muted-foreground/70">
                            Extend Duration
                        </Label>
                        <ToggleGroup
                            type="single"
                            variant="outline"
                            value={settings.extend_duration ?? '4'}
                            onValueChange={(v) => {
                                if (v) {
                                    updateSetting('extend_duration', v);
                                }
                            }}
                            className="justify-start gap-2 shadow-none"
                        >
                            {EXTEND_DURATION_OPTIONS.map((opt) => (
                                <ToggleGroupItem
                                    key={opt.value}
                                    value={opt.value}
                                    className="!rounded-lg !border !border-border/60 px-4 py-2 text-sm font-medium shadow-none"
                                >
                                    {opt.label}
                                </ToggleGroupItem>
                            ))}
                        </ToggleGroup>
                    </div>
                    <div className="space-y-1.5">
                        <Label className="text-[11px] text-muted-foreground/70">
                            Prompt (optional)
                        </Label>
                        <Textarea
                            placeholder="Describe how the video should continue..."
                            value={settings.prompt ?? ''}
                            onChange={(e) =>
                                updateSetting('prompt', e.target.value)
                            }
                            className="min-h-[72px] rounded-lg border-border/60 bg-background text-sm placeholder:text-muted-foreground/40"
                        />
                    </div>
                </div>
            )}

            {!canAfford && (
                <p className="text-xs text-destructive-foreground">
                    Not enough credits. You need {tool.creditCost} credits for
                    this tool.
                </p>
            )}

            <Button
                type="button"
                size="lg"
                className={cn(
                    'w-full rounded-xl font-semibold text-white transition-all duration-300',
                    isBusy
                        ? 'bg-muted text-muted-foreground'
                        : 'bg-gradient-to-r from-amber-accent to-amber-accent-dark shadow-md shadow-amber-accent/20 hover:shadow-lg hover:shadow-amber-accent/30',
                )}
                disabled={isBusy || !canAfford}
                onClick={handleApply}
            >
                {isBusy ? (
                    <>
                        <Loader2 className="mr-2 size-4 animate-spin" />
                        {isProcessing ? 'Processing...' : 'Applying...'}
                    </>
                ) : (
                    <>
                        <Zap className="mr-1.5 size-3.5" />
                        Apply {toolLabel}
                    </>
                )}
            </Button>
        </div>
    );
}
