import { Link } from '@inertiajs/react';
import type { ComponentProps } from 'react';
import { cn } from '@/lib/utils';

type Props = ComponentProps<typeof Link>;

export default function TextLink({
    className = '',
    children,
    ...props
}: Props) {
    return (
        <Link
            className={cn(
                'font-medium text-amber-accent underline decoration-amber-accent/30 underline-offset-4 transition-colors duration-200 ease-out hover:decoration-amber-accent! dark:text-amber-accent-light dark:decoration-amber-accent-light/30',
                className,
            )}
            {...props}
        >
            {children}
        </Link>
    );
}
