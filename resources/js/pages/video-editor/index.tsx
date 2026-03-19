import { Head, router } from '@inertiajs/react';
import { ArrowLeft, Film, PenLine, Plus, Video } from 'lucide-react';
import { useState } from 'react';

import { Button } from '@/components/ui/button';
import { SourcePicker } from '@/components/video-editor/source-picker';
import { SourceUpload } from '@/components/video-editor/source-upload';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { index, show } from '@/routes/video-editor';
import type {
    BreadcrumbItem,
    GeneratedVideo,
    VideoEditSession,
    VideoToolDefinition,
} from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Video Editor',
        href: index(),
    },
];

interface Props {
    sessions: VideoEditSession[];
    generatedVideos: GeneratedVideo[];
    credits: number;
    tools: VideoToolDefinition[];
}

type SourceTab = 'upload' | 'generated';

export default function VideoEditorIndex({ sessions, generatedVideos }: Props) {
    const [activeTab, setActiveTab] = useState<SourceTab>('upload');
    const [showNewSession, setShowNewSession] = useState(false);

    function openSession(session: VideoEditSession) {
        router.visit(show.url({ session: session.id }));
    }

    if (sessions.length > 0 && !showNewSession) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Video Editor" />
                <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                    {/* Header */}
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-xl font-semibold tracking-tight">
                                Recent Edit Sessions
                            </h1>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Continue editing or start a new session.
                            </p>
                        </div>
                        <Button
                            className="gap-2 rounded-xl bg-amber-accent text-white hover:bg-amber-accent-dark"
                            onClick={() => setShowNewSession(true)}
                        >
                            <Plus className="size-4" />
                            New Edit
                        </Button>
                    </div>

                    {/* Sessions grid */}
                    <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                        {sessions.map((session) => (
                            <button
                                key={session.id}
                                type="button"
                                onClick={() => openSession(session)}
                                className="group overflow-hidden rounded-2xl border border-border/60 bg-card text-left transition-all duration-300 hover:border-border hover:shadow-lg hover:shadow-warm-900/5 dark:hover:shadow-warm-900/20"
                            >
                                <div className="aspect-video overflow-hidden">
                                    <video
                                        src={session.source_url}
                                        className="size-full object-cover"
                                        muted
                                    />
                                </div>
                                <div className="p-3">
                                    <div className="flex items-center gap-1.5">
                                        <PenLine className="size-3 text-muted-foreground/60" />
                                        <span className="text-xs text-muted-foreground">
                                            {session.steps.length} step
                                            {session.steps.length !== 1
                                                ? 's'
                                                : ''}
                                        </span>
                                    </div>
                                    <p className="mt-1 text-[11px] text-muted-foreground/60">
                                        {new Date(
                                            session.created_at,
                                        ).toLocaleDateString()}
                                    </p>
                                </div>
                            </button>
                        ))}

                        {/* New session tile */}
                        <button
                            type="button"
                            onClick={() => setShowNewSession(true)}
                            className="flex aspect-video flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed border-border/60 transition-colors hover:border-amber-accent/40 hover:bg-amber-accent/5"
                        >
                            <div className="flex size-10 items-center justify-center rounded-xl bg-muted">
                                <Plus className="size-5 text-muted-foreground/60" />
                            </div>
                            <span className="text-xs font-medium text-muted-foreground">
                                New Session
                            </span>
                        </button>
                    </div>
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Video Editor" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 lg:flex-row">
                <div className="w-full shrink-0 lg:w-[460px]">
                    <div className="overflow-hidden rounded-2xl border border-border/60 bg-card">
                        {/* Header */}
                        <div className="flex items-center gap-3 border-b border-border/60 px-6 py-5">
                            <div className="flex size-9 items-center justify-center rounded-xl bg-gradient-to-br from-amber-accent/20 to-amber-accent/5">
                                <Film className="size-4.5 text-amber-accent" />
                            </div>
                            <div className="flex-1">
                                <h2 className="font-semibold tracking-tight">
                                    AI Video Editor
                                </h2>
                                <p className="text-xs text-muted-foreground">
                                    Edit, enhance, and transform your videos
                                </p>
                            </div>
                            {sessions.length > 0 && (
                                <button
                                    type="button"
                                    onClick={() => setShowNewSession(false)}
                                    className="flex items-center gap-1.5 text-xs font-medium text-muted-foreground transition-colors hover:text-foreground"
                                >
                                    <ArrowLeft className="size-3.5" />
                                    Back
                                </button>
                            )}
                        </div>

                        {/* Source selection tabs */}
                        <div className="p-6">
                            <div className="flex gap-1 rounded-xl bg-muted p-1">
                                <button
                                    type="button"
                                    onClick={() => setActiveTab('upload')}
                                    className={cn(
                                        'flex-1 rounded-lg px-3 py-2 text-sm font-medium transition-all',
                                        activeTab === 'upload'
                                            ? 'bg-background text-foreground shadow-sm'
                                            : 'text-muted-foreground hover:text-foreground',
                                    )}
                                >
                                    Upload Video
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setActiveTab('generated')}
                                    className={cn(
                                        'flex-1 rounded-lg px-3 py-2 text-sm font-medium transition-all',
                                        activeTab === 'generated'
                                            ? 'bg-background text-foreground shadow-sm'
                                            : 'text-muted-foreground hover:text-foreground',
                                    )}
                                >
                                    From Generator
                                </button>
                            </div>
                            <div className="mt-4">
                                {activeTab === 'upload' ? (
                                    <SourceUpload />
                                ) : (
                                    <SourcePicker videos={generatedVideos} />
                                )}
                            </div>
                        </div>
                    </div>
                </div>

                {/* Empty state / info panel */}
                <div className="flex flex-1 flex-col items-center justify-center rounded-2xl border border-dashed border-border/60 p-16 text-center">
                    <div className="flex size-16 items-center justify-center rounded-2xl bg-warm-100 dark:bg-warm-800">
                        <Video className="size-7 text-muted-foreground/40" />
                    </div>
                    <h3 className="mt-5 text-base font-semibold tracking-tight">
                        No video selected
                    </h3>
                    <p className="mt-1.5 max-w-xs text-sm leading-relaxed text-muted-foreground">
                        Upload a video or select one from your generated videos
                        to begin AI-powered editing.
                    </p>
                </div>
            </div>
        </AppLayout>
    );
}
