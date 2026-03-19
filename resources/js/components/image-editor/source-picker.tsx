import { router } from '@inertiajs/react';
import { CheckCircle2, ImageIcon } from 'lucide-react';
import { useState } from 'react';

import { cn } from '@/lib/utils';
import { store } from '@/routes/image-editor';
import type { GeneratedImage } from '@/types';

interface Props {
    images: GeneratedImage[];
}

export function SourcePicker({ images }: Props) {
    const [selectedId, setSelectedId] = useState<number | null>(null);
    const [submitting, setSubmitting] = useState(false);

    function handleSelect(image: GeneratedImage) {
        if (submitting) {
            return;
        }

        setSelectedId(image.id);
        setSubmitting(true);

        router.post(
            store.url(),
            {
                source_type: 'generated',
                generated_image_id: image.id,
            },
            {
                onError: () => {
                    setSelectedId(null);
                    setSubmitting(false);
                },
            },
        );
    }

    if (images.length === 0) {
        return (
            <div className="flex flex-col items-center justify-center gap-3 rounded-2xl border border-dashed border-border/60 p-10 text-center">
                <div className="flex size-12 items-center justify-center rounded-xl bg-muted">
                    <ImageIcon className="size-5 text-muted-foreground/40" />
                </div>
                <div>
                    <p className="text-sm font-medium">
                        No generated images yet
                    </p>
                    <p className="mt-1 text-xs text-muted-foreground">
                        Generate some images first, then come back to edit them.
                    </p>
                </div>
            </div>
        );
    }

    return (
        <div className="grid grid-cols-3 gap-3">
            {images.map((image) => (
                <button
                    key={image.id}
                    type="button"
                    disabled={submitting}
                    onClick={() => handleSelect(image)}
                    className={cn(
                        'group relative overflow-hidden rounded-xl border transition-all duration-200',
                        selectedId === image.id
                            ? 'border-amber-accent ring-2 ring-amber-accent/30'
                            : 'border-border/60 hover:border-amber-accent/40 hover:shadow-md',
                        submitting && selectedId !== image.id && 'opacity-50',
                    )}
                >
                    <div className="aspect-square overflow-hidden">
                        {image.file_url ? (
                            <img
                                src={image.file_url}
                                alt={image.prompt}
                                className="size-full object-cover transition-transform duration-300 group-hover:scale-105"
                            />
                        ) : (
                            <div className="flex size-full items-center justify-center bg-muted">
                                <ImageIcon className="size-5 text-muted-foreground/40" />
                            </div>
                        )}
                    </div>
                    {selectedId === image.id && (
                        <div className="absolute inset-0 flex items-center justify-center bg-amber-accent/20">
                            <CheckCircle2 className="size-6 text-amber-accent" />
                        </div>
                    )}
                </button>
            ))}
        </div>
    );
}
