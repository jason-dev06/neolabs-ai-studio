import { ChevronRight, Lightbulb } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';

const tips = [
    'Describe camera movements like "slow pan", "tracking shot", or "dolly zoom" for cinematic results.',
    'Include lighting details such as "golden hour", "neon-lit", or "dramatic shadows" to set the mood.',
    'Mention motion elements like "flowing water", "swaying trees", or "walking through a crowd".',
    'Use Fast quality for quick previews; Standard for final, high-fidelity output.',
    'Shorter durations (4s) work well for loops and social clips; longer (8s) for narrative scenes.',
];

export function ProTipsCard() {
    const [index, setIndex] = useState(0);

    const next = useCallback(() => {
        setIndex((prev) => (prev + 1) % tips.length);
    }, []);

    useEffect(() => {
        const timer = setInterval(next, 8000);

        return () => clearInterval(timer);
    }, [next]);

    return (
        <div className="overflow-hidden rounded-2xl border border-border/60 bg-card">
            <div className="flex items-center justify-between border-b border-border/60 px-5 py-3">
                <div className="flex items-center gap-2.5">
                    <Lightbulb className="size-3.5 text-amber-accent" />
                    <span className="text-xs font-medium">Tip</span>
                </div>
                <button
                    type="button"
                    onClick={next}
                    className="flex items-center gap-0.5 text-[11px] text-muted-foreground/50 transition-colors hover:text-muted-foreground"
                >
                    Next
                    <ChevronRight className="size-3" />
                </button>
            </div>
            <div className="relative min-h-[60px] px-5 py-4">
                <p
                    key={index}
                    className="animate-in text-xs leading-relaxed text-muted-foreground duration-300 fade-in-0 slide-in-from-right-2"
                >
                    {tips[index]}
                </p>
                {/* Progress dots */}
                <div className="mt-3 flex gap-1">
                    {tips.map((_, i) => (
                        <button
                            key={i}
                            type="button"
                            onClick={() => setIndex(i)}
                            className="group/dot p-0.5"
                        >
                            <div
                                className={`h-1 rounded-full transition-all duration-300 ${
                                    i === index
                                        ? 'w-4 bg-amber-accent/50'
                                        : 'w-1 bg-border group-hover/dot:bg-muted-foreground/30'
                                }`}
                            />
                        </button>
                    ))}
                </div>
            </div>
        </div>
    );
}
