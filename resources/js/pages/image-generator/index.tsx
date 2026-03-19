import { Head, router } from '@inertiajs/react';
import { useEffect, useRef } from 'react';

import { CreateImageForm } from '@/components/image-generator/create-image-form';
import { ImageGallery } from '@/components/image-generator/image-gallery';
import AppLayout from '@/layouts/app-layout';
import { index } from '@/routes/image-generator';
import type { BreadcrumbItem, GeneratedImage } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Image Generator',
        href: index(),
    },
];

interface Props {
    images: GeneratedImage[];
    credits: number;
}

export default function ImageGenerator({ images, credits }: Props) {
    const intervalRef = useRef<ReturnType<typeof setInterval> | null>(null);

    const hasPending = images.some(
        (img) => img.status === 'pending' || img.status === 'processing',
    );

    useEffect(() => {
        if (hasPending) {
            intervalRef.current = setInterval(() => {
                router.reload({ only: ['images', 'credits'] });
            }, 3000);
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
            <Head title="Image Generator" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 lg:flex-row">
                <div className="w-full shrink-0 lg:w-[380px]">
                    <CreateImageForm credits={credits} />
                </div>
                <div className="flex-1">
                    <ImageGallery images={images} />
                </div>
            </div>
        </AppLayout>
    );
}
