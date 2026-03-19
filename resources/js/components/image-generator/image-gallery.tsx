import { ImageIcon } from 'lucide-react';

import { ImageCard } from '@/components/image-generator/image-card';
import type { GeneratedImage } from '@/types';

export function ImageGallery({ images }: { images: GeneratedImage[] }) {
    if (images.length === 0) {
        return (
            <div className="flex flex-1 flex-col items-center justify-center rounded-2xl border border-dashed border-border/60 p-16 text-center">
                <div className="flex size-16 items-center justify-center rounded-2xl bg-warm-100 dark:bg-warm-800">
                    <ImageIcon className="size-7 text-muted-foreground/40" />
                </div>
                <h3 className="mt-5 text-base font-semibold tracking-tight">
                    No images yet
                </h3>
                <p className="mt-1.5 max-w-xs text-sm leading-relaxed text-muted-foreground">
                    Describe what you want to see, and AI will bring it to life.
                </p>
            </div>
        );
    }

    return (
        <div className="grid grid-cols-2 gap-4 lg:grid-cols-3">
            {images.map((image) => (
                <ImageCard key={image.id} image={image} />
            ))}
        </div>
    );
}
