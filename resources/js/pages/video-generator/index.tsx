import { Head, router } from '@inertiajs/react';
import { useEffect, useRef } from 'react';

import { CreateVideoForm } from '@/components/video-generator/create-video-form';
import { VideoGallery } from '@/components/video-generator/video-gallery';
import AppLayout from '@/layouts/app-layout';
import { index } from '@/routes/video-generator';
import type { BreadcrumbItem, GeneratedVideo } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Video Generator',
        href: index(),
    },
];

interface Props {
    videos: GeneratedVideo[];
    credits: number;
}

export default function VideoGenerator({ videos, credits }: Props) {
    const intervalRef = useRef<ReturnType<typeof setInterval> | null>(null);

    const hasPending = videos.some(
        (v) => v.status === 'pending' || v.status === 'processing',
    );

    useEffect(() => {
        if (hasPending) {
            intervalRef.current = setInterval(() => {
                router.reload({ only: ['videos', 'credits'] });
            }, 5000);
        }

        return () => {
            if (intervalRef.current) {
                clearInterval(intervalRef.current);
                intervalRef.current = null;
            }
        };
    }, [hasPending]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Video Generator" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 lg:flex-row">
                <div className="w-full shrink-0 lg:w-[380px]">
                    <CreateVideoForm credits={credits} />
                </div>
                <div className="flex-1">
                    <VideoGallery videos={videos} />
                </div>
            </div>
        </AppLayout>
    );
}
