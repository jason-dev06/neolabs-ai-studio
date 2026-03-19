import { Lightbulb } from 'lucide-react';

const tips = [
    'Be specific about style, lighting, and composition for better results.',
    'Include details like "photorealistic", "watercolor", or "3D render" to guide the style.',
    'Mention camera angles like "bird\'s eye view" or "close-up" for unique perspectives.',
    'Use higher quality tiers for complex scenes with fine details.',
];

export function ProTipsCard() {
    return (
        <div className="overflow-hidden rounded-2xl border border-border/60 bg-card">
            <div className="flex items-center gap-2.5 border-b border-border/60 px-5 py-3">
                <Lightbulb className="size-3.5 text-amber-accent" />
                <span className="text-xs font-medium">Tips</span>
            </div>
            <ul className="space-y-2.5 p-5">
                {tips.map((tip, i) => (
                    <li
                        key={i}
                        className="flex gap-2.5 text-xs leading-relaxed text-muted-foreground"
                    >
                        <span className="mt-1.5 block size-1 shrink-0 rounded-full bg-amber-accent/40" />
                        {tip}
                    </li>
                ))}
            </ul>
        </div>
    );
}
