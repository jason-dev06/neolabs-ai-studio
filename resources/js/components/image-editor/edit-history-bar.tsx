import { router } from '@inertiajs/react';
import { Coins, Redo2, Undo2 } from 'lucide-react';
import { useState } from 'react';

import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { redo, undo } from '@/routes/image-editor';
import type { ImageEditSession } from '@/types';

interface Props {
    session: ImageEditSession;
    credits: number;
}

export function EditHistoryBar({ session, credits }: Props) {
    const [undoing, setUndoing] = useState(false);
    const [redoing, setRedoing] = useState(false);

    const totalSteps = session.steps.filter(
        (s) => s.status === 'completed',
    ).length;
    const currentStepIndex = session.current_step;

    const canUndo = currentStepIndex > 0;
    const canRedo = currentStepIndex < totalSteps;

    function handleUndo() {
        if (!canUndo || undoing) {
            return;
        }

        setUndoing(true);
        router.post(
            undo.url({ session: session.id }),
            {},
            {
                preserveScroll: true,
                onFinish: () => setUndoing(false),
            },
        );
    }

    function handleRedo() {
        if (!canRedo || redoing) {
            return;
        }

        setRedoing(true);
        router.post(
            redo.url({ session: session.id }),
            {},
            {
                preserveScroll: true,
                onFinish: () => setRedoing(false),
            },
        );
    }

    return (
        <div className="flex items-center gap-2.5">
            {/* Credits badge */}
            <div className="flex items-center gap-1.5 rounded-full border border-amber-accent/20 bg-amber-accent/5 px-3 py-1.5 dark:border-amber-accent/15 dark:bg-amber-accent/10">
                <Coins className="size-3.5 text-amber-accent" />
                <span className="text-xs font-bold text-amber-accent tabular-nums">
                    {credits}
                </span>
            </div>

            {/* Step dots */}
            {totalSteps > 0 && (
                <div className="flex items-center gap-1.5 px-1">
                    {Array.from({ length: totalSteps }, (_, i) => (
                        <div
                            key={i}
                            className={cn(
                                'size-1.5 rounded-full transition-all duration-300',
                                i < currentStepIndex
                                    ? 'bg-amber-accent shadow-sm shadow-amber-accent/30'
                                    : 'bg-border',
                            )}
                        />
                    ))}
                    <span className="ml-1 text-[10px] font-semibold text-muted-foreground/50 tabular-nums">
                        {currentStepIndex}/{totalSteps}
                    </span>
                </div>
            )}

            {/* Divider */}
            <div className="h-5 w-px bg-border/60" />

            {/* Undo / Redo */}
            <div className="flex items-center gap-0.5">
                <Button
                    variant="ghost"
                    size="icon"
                    className="size-8 rounded-lg text-muted-foreground/60 hover:text-foreground"
                    disabled={!canUndo || undoing || redoing}
                    onClick={handleUndo}
                    title="Undo"
                >
                    <Undo2 className="size-3.5" />
                </Button>
                <Button
                    variant="ghost"
                    size="icon"
                    className="size-8 rounded-lg text-muted-foreground/60 hover:text-foreground"
                    disabled={!canRedo || undoing || redoing}
                    onClick={handleRedo}
                    title="Redo"
                >
                    <Redo2 className="size-3.5" />
                </Button>
            </div>
        </div>
    );
}
