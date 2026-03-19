import type { ImgHTMLAttributes } from 'react';

import { cn } from '@/lib/utils';

export default function AppLogoIcon({
    className,
    ...props
}: ImgHTMLAttributes<HTMLImageElement>) {
    return (
        <img
            src="/images/logo-icon.png"
            alt="NeoLabs AI"
            className={cn('object-contain', className)}
            {...props}
        />
    );
}
