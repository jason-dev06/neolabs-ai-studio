import { router } from '@inertiajs/react';
import { Coins, Loader2, Zap } from 'lucide-react';
import { useState } from 'react';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { cn } from '@/lib/utils';
import { applyTool } from '@/routes/image-editor';
import type { ImageEditorToolValue, ToolDefinition } from '@/types';

interface Props {
    sessionId: number;
    tool: ToolDefinition;
    credits: number;
    isProcessing: boolean;
}

const STYLE_OPTIONS = [
    { value: 'painterly', label: 'Painterly' },
    { value: 'watercolor', label: 'Watercolor' },
    { value: 'sketch', label: 'Sketch' },
    { value: 'anime', label: 'Anime' },
    { value: 'oil_painting', label: 'Oil Painting' },
    { value: 'pixel_art', label: 'Pixel Art' },
    { value: 'photorealistic', label: 'Photorealistic' },
    { value: 'pop-art', label: 'Pop Art' },
    { value: 'impressionist', label: 'Impressionist' },
    { value: 'cubist', label: 'Cubist' },
];

const EXTEND_DIRECTIONS = [
    { value: 'up', label: 'Up' },
    { value: 'down', label: 'Down' },
    { value: 'left', label: 'Left' },
    { value: 'right', label: 'Right' },
    { value: 'all', label: 'All Sides' },
];

const UPSCALE_FACTORS = [
    { value: '2', label: '2x' },
    { value: '4', label: '4x' },
];

function getDefaultSettings(
    tool: ImageEditorToolValue,
): Record<string, string> {
    switch (tool) {
        case 'inpaint':
            return { prompt: '' };
        case 'erase_object':
            return { erase_prompt: '' };
        case 'style_transfer':
            return { style: 'painterly' };
        case 'extend':
            return { direction: 'all' };
        case 'upscale':
            return { scale_factor: '2' };
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

            {tool.value === 'inpaint' && (
                <div className="space-y-2">
                    <Label className="text-xs text-muted-foreground">
                        What would you like to add or change?
                    </Label>
                    <Input
                        placeholder="e.g. a red umbrella, a sunset sky..."
                        value={settings.prompt ?? ''}
                        onChange={(e) =>
                            updateSetting('prompt', e.target.value)
                        }
                        className="rounded-xl border-border/60 bg-background text-sm placeholder:text-muted-foreground/40"
                    />
                </div>
            )}

            {tool.value === 'erase_object' && (
                <div className="space-y-2">
                    <Label className="text-xs text-muted-foreground">
                        What would you like to erase?
                    </Label>
                    <Input
                        placeholder="e.g. the person in the background..."
                        value={settings.erase_prompt ?? ''}
                        onChange={(e) =>
                            updateSetting('erase_prompt', e.target.value)
                        }
                        className="rounded-xl border-border/60 bg-background text-sm placeholder:text-muted-foreground/40"
                    />
                </div>
            )}

            {tool.value === 'style_transfer' && (
                <div className="space-y-2">
                    <Label className="text-xs text-muted-foreground">
                        Style
                    </Label>
                    <ToggleGroup
                        type="single"
                        variant="outline"
                        value={settings.style ?? 'painterly'}
                        onValueChange={(v) => {
                            if (v) {
                                updateSetting('style', v);
                            }
                        }}
                        className="flex-wrap justify-start gap-1.5 shadow-none"
                    >
                        {STYLE_OPTIONS.map((opt) => (
                            <ToggleGroupItem
                                key={opt.value}
                                value={opt.value}
                                className="!rounded-xl !border !border-border/60 px-2.5 py-1.5 text-xs shadow-none"
                            >
                                {opt.label}
                            </ToggleGroupItem>
                        ))}
                    </ToggleGroup>
                </div>
            )}

            {tool.value === 'extend' && (
                <div className="space-y-2">
                    <Label className="text-xs text-muted-foreground">
                        Direction
                    </Label>
                    <ToggleGroup
                        type="single"
                        variant="outline"
                        value={settings.direction ?? 'all'}
                        onValueChange={(v) => {
                            if (v) {
                                updateSetting('direction', v);
                            }
                        }}
                        className="flex-wrap justify-start gap-1.5 shadow-none"
                    >
                        {EXTEND_DIRECTIONS.map((opt) => (
                            <ToggleGroupItem
                                key={opt.value}
                                value={opt.value}
                                className="!rounded-xl !border !border-border/60 px-2.5 py-1.5 text-xs shadow-none"
                            >
                                {opt.label}
                            </ToggleGroupItem>
                        ))}
                    </ToggleGroup>
                </div>
            )}

            {tool.value === 'upscale' && (
                <div className="space-y-2">
                    <Label className="text-xs text-muted-foreground">
                        Scale Factor
                    </Label>
                    <ToggleGroup
                        type="single"
                        variant="outline"
                        value={settings.scale_factor ?? '2'}
                        onValueChange={(v) => {
                            if (v) {
                                updateSetting('scale_factor', v);
                            }
                        }}
                        className="justify-start gap-2 shadow-none"
                    >
                        {UPSCALE_FACTORS.map((opt) => (
                            <ToggleGroupItem
                                key={opt.value}
                                value={opt.value}
                                className="!rounded-xl !border !border-border/60 px-4 py-2 text-sm shadow-none"
                            >
                                {opt.label}
                            </ToggleGroupItem>
                        ))}
                    </ToggleGroup>
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
