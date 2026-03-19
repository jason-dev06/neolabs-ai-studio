import { router } from '@inertiajs/react';
import {
    AlertCircle,
    Download,
    Loader2,
    Trash2,
    X,
    ZoomIn,
} from 'lucide-react';
import { useState } from 'react';
import type { MouseEvent } from 'react';

import { destroy } from '@/actions/App/Http/Controllers/ImageGenerator/ImageGeneratorController';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
} from '@/components/ui/dialog';
import type { GeneratedImage } from '@/types';

export function ImageCard({ image }: { image: GeneratedImage }) {
    const [previewOpen, setPreviewOpen] = useState(false);
    const [confirmOpen, setConfirmOpen] = useState(false);
    const [deleting, setDeleting] = useState(false);

    const isLoading =
        image.status === 'pending' || image.status === 'processing';
    const isFailed = image.status === 'failed';

    function handleDelete() {
        setDeleting(true);
        router.delete(destroy.url({ generatedImage: image.id }), {
            preserveScroll: true,
            onFinish: () => {
                setDeleting(false);
                setConfirmOpen(false);
            },
        });
    }

    function openConfirm(e: MouseEvent) {
        e.stopPropagation();
        setConfirmOpen(true);
    }

    function openPreview(e: MouseEvent) {
        e.stopPropagation();
        setPreviewOpen(true);
    }

    return (
        <>
            <div
                className="group relative cursor-pointer overflow-hidden rounded-2xl border border-border/60 bg-card transition-all duration-300 hover:border-border hover:shadow-lg hover:shadow-warm-900/5 dark:hover:shadow-warm-900/20"
                onClick={() => {
                    if (image.file_url) {
                        setPreviewOpen(true);
                    }
                }}
            >
                {/* Image area */}
                <div className="relative aspect-square overflow-hidden">
                    {isLoading && (
                        <div className="flex size-full items-center justify-center bg-gradient-to-br from-warm-100 to-warm-200/50 dark:from-warm-800 dark:to-warm-900/50">
                            <div className="text-center">
                                <div className="relative mx-auto size-10">
                                    <div className="absolute inset-0 animate-ping rounded-full bg-amber-accent/20" />
                                    <div className="relative flex size-full items-center justify-center rounded-full bg-warm-200 dark:bg-warm-700">
                                        <Loader2 className="size-5 animate-spin text-amber-accent" />
                                    </div>
                                </div>
                                <p className="mt-3 text-xs font-medium text-muted-foreground">
                                    Creating your image...
                                </p>
                            </div>
                        </div>
                    )}

                    {isFailed && (
                        <div className="flex size-full items-center justify-center bg-gradient-to-br from-red-50 to-red-100/50 dark:from-red-950/30 dark:to-red-900/20">
                            <div className="text-center">
                                <div className="mx-auto flex size-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/40">
                                    <AlertCircle className="size-5 text-destructive-foreground" />
                                </div>
                                <p className="mt-3 text-xs font-medium text-destructive-foreground">
                                    Generation failed
                                </p>
                            </div>
                        </div>
                    )}

                    {image.file_url && (
                        <>
                            <img
                                src={image.file_url}
                                alt={image.prompt}
                                className="size-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.03]"
                            />
                            {/* Hover overlay with actions */}
                            <div className="absolute inset-0 flex items-center justify-center gap-2 bg-black/0 transition-all duration-300 group-hover:bg-black/40">
                                <Button
                                    variant="secondary"
                                    size="icon"
                                    className="size-9 translate-y-2 rounded-full bg-white/90 text-warm-900 opacity-0 shadow-lg backdrop-blur-sm transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100 hover:bg-white dark:bg-warm-900/90 dark:text-warm-100 dark:hover:bg-warm-800"
                                    onClick={openPreview}
                                >
                                    <ZoomIn className="size-4" />
                                </Button>
                                {image.file_url && (
                                    <a
                                        href={image.file_url}
                                        download
                                        onClick={(e) => e.stopPropagation()}
                                        className="inline-flex size-9 translate-y-2 items-center justify-center rounded-full bg-white/90 text-warm-900 opacity-0 shadow-lg backdrop-blur-sm transition-all delay-75 duration-300 group-hover:translate-y-0 group-hover:opacity-100 hover:bg-white dark:bg-warm-900/90 dark:text-warm-100 dark:hover:bg-warm-800"
                                    >
                                        <Download className="size-4" />
                                    </a>
                                )}
                                <Button
                                    variant="secondary"
                                    size="icon"
                                    className="size-9 translate-y-2 rounded-full bg-white/90 text-red-600 opacity-0 shadow-lg backdrop-blur-sm transition-all delay-150 duration-300 group-hover:translate-y-0 group-hover:opacity-100 hover:bg-red-50 dark:bg-warm-900/90 dark:text-red-400 dark:hover:bg-red-950/50"
                                    onClick={openConfirm}
                                >
                                    <Trash2 className="size-4" />
                                </Button>
                            </div>
                        </>
                    )}

                    {/* Delete button for non-completed states */}
                    {!image.file_url && (
                        <Button
                            variant="ghost"
                            size="icon"
                            className="absolute top-2 right-2 size-7 rounded-full text-muted-foreground opacity-0 transition-opacity group-hover:opacity-100 hover:bg-destructive/10 hover:text-destructive-foreground"
                            onClick={openConfirm}
                        >
                            <X className="size-3.5" />
                        </Button>
                    )}
                </div>

                {/* Card footer */}
                <div className="space-y-1.5 p-3">
                    <div className="flex items-center justify-between">
                        <span className="inline-flex items-center gap-1.5 text-[11px] font-medium">
                            {isLoading && (
                                <>
                                    <span className="size-1.5 animate-pulse rounded-full bg-amber-accent" />
                                    <span className="text-amber-accent">
                                        Processing
                                    </span>
                                </>
                            )}
                            {isFailed && (
                                <>
                                    <span className="size-1.5 rounded-full bg-destructive-foreground" />
                                    <span className="text-destructive-foreground">
                                        Failed
                                    </span>
                                </>
                            )}
                            {image.status === 'completed' && (
                                <>
                                    <span className="size-1.5 rounded-full bg-emerald-500" />
                                    <span className="text-muted-foreground">
                                        Completed
                                    </span>
                                </>
                            )}
                        </span>
                        <span className="text-[11px] text-muted-foreground/60 tabular-nums">
                            {image.credit_cost} cr
                        </span>
                    </div>
                    <p className="line-clamp-2 text-xs leading-relaxed text-muted-foreground">
                        {image.prompt}
                    </p>
                </div>
            </div>

            {/* Image preview dialog */}
            <Dialog open={previewOpen} onOpenChange={setPreviewOpen}>
                <DialogContent className="max-w-3xl gap-4 overflow-hidden rounded-2xl p-0">
                    <DialogTitle className="sr-only">
                        Generated Image
                    </DialogTitle>
                    {image.file_url && (
                        <img
                            src={image.file_url}
                            alt={image.prompt}
                            className="w-full"
                        />
                    )}
                    <div className="flex items-start justify-between gap-4 px-6 pb-6">
                        <p className="flex-1 text-sm leading-relaxed text-muted-foreground">
                            {image.prompt}
                        </p>
                        <div className="flex shrink-0 gap-2">
                            {image.file_url && (
                                <Button
                                    variant="outline"
                                    size="sm"
                                    className="gap-1.5 rounded-lg"
                                    asChild
                                >
                                    <a href={image.file_url} download>
                                        <Download className="size-3.5" />
                                        Download
                                    </a>
                                </Button>
                            )}
                            <Button
                                variant="outline"
                                size="sm"
                                className="gap-1.5 rounded-lg text-destructive-foreground hover:bg-destructive/10"
                                onClick={() => {
                                    setPreviewOpen(false);
                                    setConfirmOpen(true);
                                }}
                            >
                                <Trash2 className="size-3.5" />
                                Delete
                            </Button>
                        </div>
                    </div>
                </DialogContent>
            </Dialog>

            {/* Delete confirmation dialog */}
            <Dialog open={confirmOpen} onOpenChange={setConfirmOpen}>
                <DialogContent className="rounded-2xl">
                    <DialogTitle>Delete this image?</DialogTitle>
                    <DialogDescription>
                        This image will be permanently deleted. This action
                        cannot be undone.
                    </DialogDescription>
                    <DialogFooter className="gap-2">
                        <DialogClose asChild>
                            <Button variant="secondary" className="rounded-lg">
                                Cancel
                            </Button>
                        </DialogClose>
                        <Button
                            variant="destructive"
                            className="rounded-lg"
                            disabled={deleting}
                            onClick={handleDelete}
                        >
                            {deleting && (
                                <Loader2 className="mr-2 size-4 animate-spin" />
                            )}
                            Delete
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}
