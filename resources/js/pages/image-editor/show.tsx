import { Head, router } from '@inertiajs/react';
import { Sparkles } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';

import { EditHistoryBar } from '@/components/image-editor/edit-history-bar';
import { EditorCanvas } from '@/components/image-editor/editor-canvas';
import { ToolSidebar } from '@/components/image-editor/tool-sidebar';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import AppLayout from '@/layouts/app-layout';
import { index, show } from '@/routes/image-editor';
import type { BreadcrumbItem, ImageEditSession, ToolDefinition } from '@/types';

interface Props {
    session: ImageEditSession;
    currentImageUrl: string;
    credits: number;
    tools: ToolDefinition[];
}

export default function ImageEditorShow({
    session,
    currentImageUrl,
    credits,
    tools,
}: Props) {
    const intervalRef = useRef<ReturnType<typeof setInterval> | null>(null);
    const bypassRef = useRef(false);
    const [pendingNavUrl, setPendingNavUrl] = useState<string | null>(null);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Image Editor',
            href: index(),
        },
        {
            title: `Session #${session.id}`,
            href: show.url({ session: session.id }),
        },
    ];

    const hasPendingStep = session.steps.some(
        (step) => step.status === 'pending' || step.status === 'processing',
    );

    // Poll for updates while processing
    useEffect(() => {
        if (hasPendingStep) {
            intervalRef.current = setInterval(() => {
                router.reload({
                    only: ['session', 'currentImageUrl', 'credits'],
                });
            }, 3000);
        }

        return () => {
            if (intervalRef.current) {
                clearInterval(intervalRef.current);
                intervalRef.current = null;
            }
        };
    }, [hasPendingStep]);

    // Warn before leaving while processing
    useEffect(() => {
        if (!hasPendingStep) {
            return;
        }

        const handleBeforeUnload = (e: BeforeUnloadEvent) => {
            e.preventDefault();
        };

        const removeListener = router.on('before', (event) => {
            if (bypassRef.current) {
                bypassRef.current = false;

                return;
            }

            if (
                event.detail.visit.url.href.includes(
                    `/image-editor/${session.id}`,
                )
            ) {
                return;
            }

            if (event.detail.visit.prefetch) {
                return;
            }

            event.preventDefault();
            setPendingNavUrl(event.detail.visit.url.href);
        });

        window.addEventListener('beforeunload', handleBeforeUnload);

        return () => {
            window.removeEventListener('beforeunload', handleBeforeUnload);
            removeListener();
        };
    }, [hasPendingStep, session.id]);

    const handleConfirmLeave = useCallback(() => {
        if (pendingNavUrl) {
            bypassRef.current = true;
            router.visit(pendingNavUrl);
            setPendingNavUrl(null);
        }
    }, [pendingNavUrl]);

    const handleCancelLeave = useCallback(() => {
        setPendingNavUrl(null);
    }, []);

    const completedSteps = session.steps.filter(
        (s) => s.status === 'completed',
    ).length;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Image Editor" />

            {/* Full-height editor layout */}
            <div className="flex h-[calc(100vh-theme(spacing.16))] flex-col overflow-hidden bg-background">
                {/* Top toolbar */}
                <div className="relative z-10 flex shrink-0 items-center justify-between border-b border-border/40 bg-card/80 px-5 py-2.5 backdrop-blur-sm">
                    <div className="flex items-center gap-3">
                        {/* Animated accent dot */}
                        <div className="relative flex size-8 items-center justify-center">
                            <div className="absolute inset-0 animate-pulse rounded-lg bg-amber-accent/10" />
                            <div className="relative flex size-8 items-center justify-center rounded-lg bg-gradient-to-br from-amber-accent to-amber-accent-dark shadow-sm shadow-amber-accent/20">
                                <Sparkles className="size-3.5 text-white" />
                            </div>
                        </div>
                        <div>
                            <h1 className="text-[13px] leading-none font-semibold tracking-tight">
                                AI Image Editor
                            </h1>
                            <p className="mt-1 text-[11px] leading-none text-muted-foreground/70">
                                Session #{session.id}
                                {completedSteps > 0 && (
                                    <span className="ml-1.5 text-amber-accent">
                                        &middot; {completedSteps} edit
                                        {completedSteps !== 1 ? 's' : ''}{' '}
                                        applied
                                    </span>
                                )}
                            </p>
                        </div>
                    </div>

                    <EditHistoryBar session={session} credits={credits} />
                </div>

                {/* Editor body */}
                <div className="flex flex-1 overflow-hidden">
                    {/* Left sidebar */}
                    <div className="w-[360px] shrink-0 overflow-hidden">
                        <ToolSidebar
                            session={session}
                            tools={tools}
                            credits={credits}
                            isProcessing={hasPendingStep}
                        />
                    </div>

                    {/* Canvas area */}
                    <div className="flex-1 overflow-hidden">
                        <EditorCanvas
                            session={session}
                            currentImageUrl={currentImageUrl}
                            isProcessing={hasPendingStep}
                        />
                    </div>
                </div>
            </div>
            <AlertDialog
                open={pendingNavUrl !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        handleCancelLeave();
                    }
                }}
            >
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            Leave while processing?
                        </AlertDialogTitle>
                        <AlertDialogDescription>
                            An AI tool is still processing your image. If you
                            leave now, you may lose the current operation.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel onClick={handleCancelLeave}>
                            Stay
                        </AlertDialogCancel>
                        <AlertDialogAction onClick={handleConfirmLeave}>
                            Leave
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </AppLayout>
    );
}
