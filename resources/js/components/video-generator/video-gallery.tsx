import { Clapperboard } from 'lucide-react';

import { VideoCard } from '@/components/video-generator/video-card';
import type { GeneratedVideo } from '@/types';

export function VideoGallery({ videos }: { videos: GeneratedVideo[] }) {
    if (videos.length === 0) {
        return (
            <div className="relative flex flex-1 flex-col items-center justify-center overflow-hidden rounded-2xl border border-dashed border-border/60 p-16 text-center">
                {/* Animated background grain */}
                <div className="pointer-events-none absolute inset-0 bg-gradient-to-br from-amber-accent/[0.03] via-transparent to-amber-accent/[0.02]" />

                {/* Film-strip decorative lines */}
                <div className="pointer-events-none absolute inset-y-0 left-6 w-px bg-gradient-to-b from-transparent via-border/40 to-transparent" />
                <div className="pointer-events-none absolute inset-y-0 right-6 w-px bg-gradient-to-b from-transparent via-border/40 to-transparent" />

                <div className="relative">
                    <div className="relative mx-auto size-18">
                        {/* Pulsing ring */}
                        <div className="absolute inset-0 animate-ping rounded-2xl bg-amber-accent/10 [animation-duration:3s]" />
                        <div className="relative flex size-full items-center justify-center rounded-2xl bg-gradient-to-br from-warm-100 to-warm-200/60 dark:from-warm-800 dark:to-warm-900/60">
                            <Clapperboard className="size-8 text-amber-accent/60" />
                        </div>
                    </div>
                    <h3 className="mt-6 text-base font-semibold tracking-tight">
                        No videos yet
                    </h3>
                    <p className="mt-2 max-w-[260px] text-sm leading-relaxed text-muted-foreground">
                        Describe the video you want to create and AI will bring
                        it to life.
                    </p>
                </div>
            </div>
        );
    }

    return (
        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            {videos.map((video, i) => (
                <div
                    key={video.id}
                    className="animate-in fade-in-0 fill-mode-both slide-in-from-bottom-2"
                    style={{
                        animationDelay: `${i * 60}ms`,
                        animationDuration: '400ms',
                    }}
                >
                    <VideoCard video={video} />
                </div>
            ))}
        </div>
    );
}
