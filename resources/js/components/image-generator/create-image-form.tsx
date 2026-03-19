import { useForm, usePage } from '@inertiajs/react';
import { Coins, Loader2, Sparkles } from 'lucide-react';
import type { FormEventHandler } from 'react';

import { ProTipsCard } from '@/components/image-generator/pro-tips-card';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { cn } from '@/lib/utils';
import { store } from '@/routes/image-generator';
import type { AspectRatio, QualityTier } from '@/types';

const qualityOptions: { value: QualityTier; label: string; cost: number }[] = [
    { value: 'basic', label: 'Basic', cost: 10 },
    { value: 'smart', label: 'Smart', cost: 25 },
    { value: 'genius', label: 'Genius', cost: 50 },
];

const aspectOptions: { value: AspectRatio; label: string }[] = [
    { value: '1:1', label: '1:1' },
    { value: '16:9', label: '16:9' },
    { value: '9:16', label: '9:16' },
    { value: '4:3', label: '4:3' },
    { value: '3:4', label: '3:4' },
];

const imageCountOptions = [1, 2, 3, 4];

function getCreditCost(tier: QualityTier): number {
    return qualityOptions.find((o) => o.value === tier)?.cost ?? 10;
}

export function CreateImageForm({ credits }: { credits: number }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        prompt: '',
        quality_tier: 'basic' as QualityTier,
        aspect_ratio: '1:1' as AspectRatio,
        number_of_images: 1,
    });

    const pageErrors = usePage().props.errors as Record<string, string>;
    const totalCost = getCreditCost(data.quality_tier) * data.number_of_images;
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
                            Generate Image
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
                            placeholder="A surreal landscape with floating islands above a crystal ocean at golden hour..."
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

                    <div className="space-y-2.5">
                        <Label className="text-sm">Quality</Label>
                        <ToggleGroup
                            type="single"
                            variant="outline"
                            value={data.quality_tier}
                            onValueChange={(v) => {
                                if (v) {
                                    setData('quality_tier', v as QualityTier);
                                }
                            }}
                            className="justify-start gap-2 shadow-none"
                        >
                            {qualityOptions.map((opt) => (
                                <ToggleGroupItem
                                    key={opt.value}
                                    value={opt.value}
                                    className="gap-1.5 !rounded-lg !border !border-border/60 px-3 py-2 text-sm shadow-none"
                                >
                                    {opt.label}
                                    <span className="text-muted-foreground/60">
                                        {opt.cost}cr
                                    </span>
                                </ToggleGroupItem>
                            ))}
                        </ToggleGroup>
                        {errors.quality_tier && (
                            <p className="text-xs text-destructive-foreground">
                                {errors.quality_tier}
                            </p>
                        )}
                    </div>

                    <div className="space-y-2.5">
                        <Label className="text-sm">Aspect Ratio</Label>
                        <ToggleGroup
                            type="single"
                            variant="outline"
                            value={data.aspect_ratio}
                            onValueChange={(v) => {
                                if (v) {
                                    setData('aspect_ratio', v as AspectRatio);
                                }
                            }}
                            className="justify-start gap-2 shadow-none"
                        >
                            {aspectOptions.map((opt) => (
                                <ToggleGroupItem
                                    key={opt.value}
                                    value={opt.value}
                                    className="!rounded-lg !border !border-border/60 px-3 py-2 text-sm shadow-none"
                                >
                                    {opt.label}
                                </ToggleGroupItem>
                            ))}
                        </ToggleGroup>
                        {errors.aspect_ratio && (
                            <p className="text-xs text-destructive-foreground">
                                {errors.aspect_ratio}
                            </p>
                        )}
                    </div>

                    <div className="space-y-2.5">
                        <Label className="text-sm">Images</Label>
                        <ToggleGroup
                            type="single"
                            variant="outline"
                            value={String(data.number_of_images)}
                            onValueChange={(v) => {
                                if (v) {
                                    setData('number_of_images', Number(v));
                                }
                            }}
                            className="justify-start gap-2 shadow-none"
                        >
                            {imageCountOptions.map((n) => (
                                <ToggleGroupItem
                                    key={n}
                                    value={String(n)}
                                    className="!rounded-lg !border !border-border/60 px-3 py-2 text-sm shadow-none"
                                >
                                    {n}
                                </ToggleGroupItem>
                            ))}
                        </ToggleGroup>
                        {errors.number_of_images && (
                            <p className="text-xs text-destructive-foreground">
                                {errors.number_of_images}
                            </p>
                        )}
                    </div>

                    {pageErrors.credits && (
                        <p className="text-xs text-destructive-foreground">
                            {pageErrors.credits}
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
                </form>
            </div>

            <ProTipsCard />
        </div>
    );
}
