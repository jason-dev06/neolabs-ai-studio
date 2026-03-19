import { Head, Link } from '@inertiajs/react';
import {
    ArrowRight,
    FileText,
    MessageSquare,
    Mic,
    Sparkles,
    Video,
    Wand2,
} from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { index as imageGeneratorIndex } from '@/routes/image-generator';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];

const aiTools = [
    {
        title: 'Image Generator',
        description:
            'Create stunning visuals from text prompts with AI-powered image generation.',
        icon: Wand2,
        href: imageGeneratorIndex(),
        color: 'from-amber-500/10 to-orange-500/10 dark:from-amber-500/5 dark:to-orange-500/5',
        iconColor: 'text-amber-600 dark:text-amber-400',
        available: true,
    },
    {
        title: 'Chat',
        description:
            'Have intelligent conversations powered by advanced language models.',
        icon: MessageSquare,
        href: '#',
        color: 'from-sky-500/10 to-blue-500/10 dark:from-sky-500/5 dark:to-blue-500/5',
        iconColor: 'text-sky-600 dark:text-sky-400',
        available: false,
    },
    {
        title: 'Documents',
        description:
            'Generate, summarize, and transform documents with AI assistance.',
        icon: FileText,
        href: '#',
        color: 'from-emerald-500/10 to-teal-500/10 dark:from-emerald-500/5 dark:to-teal-500/5',
        iconColor: 'text-emerald-600 dark:text-emerald-400',
        available: false,
    },
    {
        title: 'Video',
        description:
            'Create and edit video content with AI-powered tools and effects.',
        icon: Video,
        href: '#',
        color: 'from-violet-500/10 to-purple-500/10 dark:from-violet-500/5 dark:to-purple-500/5',
        iconColor: 'text-violet-600 dark:text-violet-400',
        available: false,
    },
    {
        title: 'Transcribe',
        description:
            'Convert audio and video to accurate text transcriptions instantly.',
        icon: Mic,
        href: '#',
        color: 'from-rose-500/10 to-pink-500/10 dark:from-rose-500/5 dark:to-pink-500/5',
        iconColor: 'text-rose-600 dark:text-rose-400',
        available: false,
    },
];

export default function Dashboard() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-8 p-6 md:p-8">
                {/* Welcome section */}
                <div className="space-y-2">
                    <div className="flex items-center gap-2">
                        <Sparkles className="size-5 text-amber-accent" />
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Welcome back
                        </h1>
                    </div>
                    <p className="max-w-lg text-sm leading-relaxed text-muted-foreground">
                        Choose an AI tool below to get started, or continue
                        where you left off.
                    </p>
                </div>

                {/* AI Tools grid */}
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {aiTools.map((tool) => (
                        <Link
                            key={tool.title}
                            href={tool.href}
                            className="group relative flex flex-col overflow-hidden rounded-2xl border border-border/60 bg-card transition-all duration-300 hover:border-border hover:shadow-md hover:shadow-warm-900/5 dark:hover:shadow-warm-900/20"
                        >
                            {/* Gradient background */}
                            <div
                                className={`absolute inset-0 bg-gradient-to-br ${tool.color} opacity-0 transition-opacity duration-300 group-hover:opacity-100`}
                            />

                            <div className="relative flex flex-1 flex-col gap-4 p-6">
                                <div className="flex items-start justify-between">
                                    <div
                                        className={`flex size-11 items-center justify-center rounded-xl bg-warm-100 transition-colors duration-200 group-hover:bg-warm-200/80 dark:bg-warm-800 dark:group-hover:bg-warm-700`}
                                    >
                                        <tool.icon
                                            className={`size-5 ${tool.iconColor}`}
                                        />
                                    </div>
                                    {!tool.available && (
                                        <span className="rounded-full bg-warm-200/80 px-2.5 py-0.5 text-[11px] font-medium text-warm-500 dark:bg-warm-800 dark:text-warm-400">
                                            Coming soon
                                        </span>
                                    )}
                                </div>

                                <div className="space-y-1.5">
                                    <h3 className="font-semibold tracking-tight">
                                        {tool.title}
                                    </h3>
                                    <p className="text-sm leading-relaxed text-muted-foreground">
                                        {tool.description}
                                    </p>
                                </div>

                                <div className="mt-auto flex items-center gap-1.5 pt-2 text-sm font-medium text-amber-accent opacity-0 transition-all duration-300 group-hover:opacity-100">
                                    {tool.available
                                        ? 'Get started'
                                        : 'Learn more'}
                                    <ArrowRight className="size-3.5 transition-transform duration-200 group-hover:translate-x-0.5" />
                                </div>
                            </div>
                        </Link>
                    ))}
                </div>

                {/* Quick stats / recent activity placeholder */}
                <div className="grid gap-4 md:grid-cols-2">
                    <div className="flex flex-col gap-3 rounded-2xl border border-border/60 bg-card p-6">
                        <h3 className="text-sm font-medium text-muted-foreground">
                            Recent Activity
                        </h3>
                        <div className="flex flex-1 items-center justify-center py-8">
                            <p className="text-sm text-muted-foreground/60">
                                No recent activity yet
                            </p>
                        </div>
                    </div>
                    <div className="flex flex-col gap-3 rounded-2xl border border-border/60 bg-card p-6">
                        <h3 className="text-sm font-medium text-muted-foreground">
                            Usage
                        </h3>
                        <div className="flex flex-1 items-center justify-center py-8">
                            <p className="text-sm text-muted-foreground/60">
                                Start creating to see your usage
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
