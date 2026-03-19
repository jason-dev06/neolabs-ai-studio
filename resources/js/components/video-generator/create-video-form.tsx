import { useForm, usePage } from '@inertiajs/react';
import { Coins, Loader2, Sparkles, Zap } from 'lucide-react';
import type { FormEventHandler } from 'react';

import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { ProTipsCard } from '@/components/video-generator/pro-tips-card';
import { cn } from '@/lib/utils';
import { store } from '@/routes/video-generator';
import type {
    VideoAspectRatio,
    VideoDuration,
    VideoQualityTier,
    VideoStyle,
} from '@/types';

const qualityOptions: {
    value: VideoQualityTier;
    label: string;
    description: string;
    cost: number;
    icon: typeof Zap;
}[] = [
    {
        value: 'fast',
        label: 'Fast',
        description: 'Veo 3.1 Fast',
        cost: 30,
        icon: Zap,
    },
    {
        value: 'standard',
        label: 'Standard',
        description: 'Veo 3.1',
        cost: 60,
        icon: Sparkles,
    },
];

const durationOptions: {
    value: VideoDuration;
    label: string;
    multiplier: number;
}[] = [
    { value: '4', label: '4s', multiplier: 1.0 },
    { value: '6', label: '6s', multiplier: 1.5 },
    { value: '8', label: '8s', multiplier: 2.0 },
];

const aspectOptions: {
    value: VideoAspectRatio;
    label: string;
    widthRatio: number;
    heightRatio: number;
}[] = [
    { value: '16:9', label: 'Landscape', widthRatio: 16, heightRatio: 9 },
    { value: '9:16', label: 'Portrait', widthRatio: 9, heightRatio: 16 },
];

const styleOptions: { value: VideoStyle; label: string }[] = [
    { value: 'cinematic', label: 'Cinematic' },
    { value: 'anime', label: 'Anime' },
    { value: 'documentary', label: 'Documentary' },
    { value: 'commercial', label: 'Commercial' },
    { value: 'music_video', label: 'Music Video' },
    { value: 'vlog', label: 'Vlog' },
];

function getCreditCost(
    tier: VideoQualityTier,
    duration: VideoDuration,
): number {
    const baseCost = qualityOptions.find((o) => o.value === tier)?.cost ?? 30;
    const multiplier =
        durationOptions.find((o) => o.value === duration)?.multiplier ?? 1.0;

    return Math.ceil(baseCost * multiplier);
}

export function CreateVideoForm({ credits }: { credits: number }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        prompt: '',
        quality_tier: 'fast' as VideoQualityTier,
        duration: '4' as VideoDuration,
        aspect_ratio: '16:9' as VideoAspectRatio,
        video_style: 'cinematic' as VideoStyle,
    });

    const pageErrors = usePage().props.errors as Record<string, string>;
    const totalCost = getCreditCost(data.quality_tier, data.duration);
    const canAfford = credits >= totalCost;

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(store.url(), {
            preserveScroll: true,
            onSuccess: () => reset('prompt'),
        });
    };

    return (
        <div className="space-y-4">
            <div className="overflow-hidden rounded-2xl border border-border/60 bg-card">
                {/* Header with credits */}
                <div className="flex items-center justify-between border-b border-border/60 px-6 py-5">
                    <div className="flex items-center gap-3">
                        <div className="flex size-9 items-center justify-center rounded-xl bg-gradient-to-br from-amber-accent/20 to-amber-accent/5">
                            <Sparkles className="size-4.5 text-amber-accent" />
                        </div>
                        <h2 className="font-semibold tracking-tight">
                            Create Video
                        </h2>
                    </div>
                    <div className="flex items-center gap-2 rounded-full bg-warm-100 px-3.5 py-2 dark:bg-warm-800">
                        <Coins className="size-4 text-amber-accent" />
                        <span className="text-sm font-semibold tabular-nums">
                            {credits}
                        </span>
                    </div>
                </div>

                {/* Form */}
                <form onSubmit={submit} className="space-y-6 p-6">
                    {/* Prompt */}
                    <div className="space-y-2.5">
                        <div className="flex items-center justify-between">
                            <Label htmlFor="prompt" className="text-sm">
                                Prompt
                            </Label>
                            <span
                                className={cn(
                                    'text-[11px] tabular-nums transition-colors',
                                    data.prompt.length > 900
                                        ? 'text-amber-accent'
                                        : 'text-muted-foreground/50',
                                )}
                            >
                                {data.prompt.length}/1000
                            </span>
                        </div>
                        <Textarea
                            id="prompt"
                            placeholder="A cinematic drone shot flying over a misty mountain range at sunrise, with golden light breaking through clouds..."
                            value={data.prompt}
                            onChange={(e) => setData('prompt', e.target.value)}
                            maxLength={1000}
                            rows={4}
                            className="resize-none rounded-xl border-border/60 bg-background text-sm transition-colors focus:border-amber-accent/50"
                        />
                        {errors.prompt && (
                            <p className="text-xs text-destructive-foreground">
                                {errors.prompt}
                            </p>
                        )}
                    </div>

                    {/* Quality — richer cards */}
                    <div className="space-y-2.5">
                        <Label className="text-sm">Quality</Label>
                        <div className="grid grid-cols-2 gap-2">
                            {qualityOptions.map((opt) => {
                                const Icon = opt.icon;
                                const active = data.quality_tier === opt.value;

                                return (
                                    <button
                                        key={opt.value}
                                        type="button"
                                        onClick={() =>
                                            setData('quality_tier', opt.value)
                                        }
                                        className={cn(
                                            'flex flex-col items-start gap-1 rounded-xl border px-3.5 py-3 text-left transition-all',
                                            active
                                                ? 'border-amber-accent/50 bg-amber-accent/5 ring-1 ring-amber-accent/20'
                                                : 'border-border/60 hover:border-border',
                                        )}
                                    >
                                        <div className="flex w-full items-center justify-between">
                                            <div className="flex items-center gap-1.5">
                                                <Icon
                                                    className={cn(
                                                        'size-3.5',
                                                        active
                                                            ? 'text-amber-accent'
                                                            : 'text-muted-foreground/60',
                                                    )}
                                                />
                                                <span className="text-sm font-medium">
                                                    {opt.label}
                                                </span>
                                            </div>
                                            <span className="text-[11px] text-muted-foreground/60 tabular-nums">
                                                {opt.cost}cr
                                            </span>
                                        </div>
                                        <span className="text-[11px] text-muted-foreground/50">
                                            {opt.description}
                                        </span>
                                    </button>
                                );
                            })}
                        </div>
                        {errors.quality_tier && (
                            <p className="text-xs text-destructive-foreground">
                                {errors.quality_tier}
                            </p>
                        )}
                    </div>

                    {/* Duration */}
                    <div className="space-y-2.5">
                        <Label className="text-sm">Duration</Label>
                        <ToggleGroup
                            type="single"
                            variant="outline"
                            value={data.duration}
                            onValueChange={(v) => {
                                if (v) {
                                    setData('duration', v as VideoDuration);
                                }
                            }}
                            className="justify-start gap-2 shadow-none"
                        >
                            {durationOptions.map((opt) => (
                                <ToggleGroupItem
                                    key={opt.value}
                                    value={opt.value}
                                    className="!rounded-lg !border !border-border/60 px-3 py-2 text-sm shadow-none"
                                >
                                    {opt.label}
                                </ToggleGroupItem>
                            ))}
                        </ToggleGroup>
                        {errors.duration && (
                            <p className="text-xs text-destructive-foreground">
                                {errors.duration}
                            </p>
                        )}
                    </div>

                    {/* Aspect Ratio — visual preview */}
                    <div className="space-y-2.5">
                        <Label className="text-sm">Aspect Ratio</Label>
                        <div className="flex gap-2">
                            {aspectOptions.map((opt) => {
                                const active = data.aspect_ratio === opt.value;

                                return (
                                    <button
                                        key={opt.value}
                                        type="button"
                                        onClick={() =>
                                            setData('aspect_ratio', opt.value)
                                        }
                                        className={cn(
                                            'flex items-center gap-2.5 rounded-xl border px-3.5 py-2.5 transition-all',
                                            active
                                                ? 'border-amber-accent/50 bg-amber-accent/5 ring-1 ring-amber-accent/20'
                                                : 'border-border/60 hover:border-border',
                                        )}
                                    >
                                        {/* Mini aspect-ratio preview */}
                                        <div
                                            className={cn(
                                                'rounded-[3px] border-[1.5px] transition-colors',
                                                active
                                                    ? 'border-amber-accent bg-amber-accent/10'
                                                    : 'border-muted-foreground/30 bg-muted-foreground/5',
                                            )}
                                            style={{
                                                width: `${Math.round((opt.widthRatio / Math.max(opt.widthRatio, opt.heightRatio)) * 22)}px`,
                                                height: `${Math.round((opt.heightRatio / Math.max(opt.widthRatio, opt.heightRatio)) * 22)}px`,
                                            }}
                                        />
                                        <div className="text-left">
                                            <div className="text-sm font-medium">
                                                {opt.value}
                                            </div>
                                            <div className="text-[10px] text-muted-foreground/50">
                                                {opt.label}
                                            </div>
                                        </div>
                                    </button>
                                );
                            })}
                        </div>
                        {errors.aspect_ratio && (
                            <p className="text-xs text-destructive-foreground">
                                {errors.aspect_ratio}
                            </p>
                        )}
                    </div>

                    {/* Video Style */}
                    <div className="space-y-2.5">
                        <Label className="text-sm">Video Style</Label>
                        <Select
                            value={data.video_style}
                            onValueChange={(v) =>
                                setData('video_style', v as VideoStyle)
                            }
                        >
                            <SelectTrigger className="rounded-xl border-border/60 bg-background">
                                <SelectValue placeholder="Select a style" />
                            </SelectTrigger>
                            <SelectContent>
                                {styleOptions.map((opt) => (
                                    <SelectItem
                                        key={opt.value}
                                        value={opt.value}
                                    >
                                        {opt.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.video_style && (
                            <p className="text-xs text-destructive-foreground">
                                {errors.video_style}
                            </p>
                        )}
                    </div>

                    {pageErrors.credits && (
                        <p className="text-xs text-destructive-foreground">
                            {pageErrors.credits}
                        </p>
                    )}

                    {/* Submit with cost breakdown */}
                    <div className="space-y-3">
                        {!canAfford && (
                            <p className="flex items-center gap-1.5 rounded-lg bg-destructive/5 px-3 py-2 text-xs text-destructive-foreground">
                                <Coins className="size-3.5 shrink-0" />
                                Not enough credits. You need {totalCost} but
                                have {credits}.
                            </p>
                        )}
                        <Button
                            type="submit"
                            size="lg"
                            className="w-full rounded-xl"
                            disabled={
                                processing || !data.prompt.trim() || !canAfford
                            }
                        >
                            {processing ? (
                                <>
                                    <Loader2 className="mr-2 size-4 animate-spin" />
                                    Generating...
                                </>
                            ) : (
                                <>
                                    <Sparkles className="mr-2 size-4" />
                                    Generate &mdash; {totalCost} credits
                                </>
                            )}
                        </Button>
                    </div>
                </form>
            </div>

            <ProTipsCard />
        </div>
    );
}
