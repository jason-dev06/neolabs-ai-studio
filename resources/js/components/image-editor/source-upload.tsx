import { useForm } from '@inertiajs/react';
import { ImageIcon, Loader2, Upload, X } from 'lucide-react';
import { useRef, useState } from 'react';
import type { DragEvent } from 'react';

import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { store } from '@/routes/image-editor';

const ACCEPTED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
const MAX_SIZE_MB = 10;

export function SourceUpload() {
    const [isDragging, setIsDragging] = useState(false);
    const [preview, setPreview] = useState<string | null>(null);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const { data, setData, post, processing, errors } = useForm<{
        source_type: 'upload';
        image: File | null;
    }>({
        source_type: 'upload',
        image: null,
    });

    function handleFile(file: File) {
        if (!ACCEPTED_TYPES.includes(file.type)) {
            return;
        }

        if (file.size > MAX_SIZE_MB * 1024 * 1024) {
            return;
        }

        setData('image', file);
        const url = URL.createObjectURL(file);
        setPreview(url);
    }

    function handleDrop(e: DragEvent<HTMLDivElement>) {
        e.preventDefault();
        setIsDragging(false);
        const file = e.dataTransfer.files[0];

        if (file) {
            handleFile(file);
        }
    }

    function handleDragOver(e: DragEvent<HTMLDivElement>) {
        e.preventDefault();
        setIsDragging(true);
    }

    function handleDragLeave() {
        setIsDragging(false);
    }

    function handleInputChange(e: React.ChangeEvent<HTMLInputElement>) {
        const file = e.target.files?.[0];

        if (file) {
            handleFile(file);
        }
    }

    function clearPreview() {
        setPreview(null);
        setData('image', null);

        if (fileInputRef.current) {
            fileInputRef.current.value = '';
        }
    }

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();

        if (!data.image) {
            return;
        }

        post(store.url(), {
            forceFormData: true,
        });
    }

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            {preview ? (
                <div className="relative overflow-hidden rounded-2xl border border-border/60">
                    <img
                        src={preview}
                        alt="Selected image"
                        className="h-64 w-full object-contain"
                    />
                    <Button
                        type="button"
                        variant="secondary"
                        size="icon"
                        className="absolute top-2 right-2 size-7 rounded-full bg-black/50 text-white hover:bg-black/70"
                        onClick={clearPreview}
                    >
                        <X className="size-3.5" />
                    </Button>
                </div>
            ) : (
                <div
                    className={cn(
                        'flex cursor-pointer flex-col items-center justify-center gap-4 rounded-2xl border-2 border-dashed p-12 text-center transition-colors',
                        isDragging
                            ? 'border-amber-accent/60 bg-amber-accent/5'
                            : 'border-border/60 hover:border-border hover:bg-muted/30',
                    )}
                    onDrop={handleDrop}
                    onDragOver={handleDragOver}
                    onDragLeave={handleDragLeave}
                    onClick={() => fileInputRef.current?.click()}
                >
                    <div className="flex size-14 items-center justify-center rounded-2xl bg-muted">
                        <Upload className="size-6 text-muted-foreground/60" />
                    </div>
                    <div>
                        <p className="text-sm font-medium">
                            Drop an image here
                        </p>
                        <p className="mt-1 text-xs text-muted-foreground">
                            JPG, PNG, WebP — up to {MAX_SIZE_MB}MB
                        </p>
                    </div>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        className="rounded-lg"
                        onClick={(e) => {
                            e.stopPropagation();
                            fileInputRef.current?.click();
                        }}
                    >
                        <ImageIcon className="mr-2 size-3.5" />
                        Browse files
                    </Button>
                </div>
            )}

            <input
                ref={fileInputRef}
                type="file"
                accept={ACCEPTED_TYPES.join(',')}
                className="hidden"
                onChange={handleInputChange}
            />

            {errors.image && (
                <p className="text-xs text-destructive-foreground">
                    {errors.image}
                </p>
            )}

            {preview && (
                <Button
                    type="submit"
                    size="lg"
                    className="w-full rounded-xl bg-amber-accent text-white hover:bg-amber-accent-dark"
                    disabled={processing || !data.image}
                >
                    {processing ? (
                        <>
                            <Loader2 className="mr-2 size-4 animate-spin" />
                            Creating session...
                        </>
                    ) : (
                        <>
                            <Upload className="mr-2 size-4" />
                            Start Editing
                        </>
                    )}
                </Button>
            )}
        </form>
    );
}
