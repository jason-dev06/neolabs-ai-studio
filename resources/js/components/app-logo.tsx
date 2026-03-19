import AppLogoIcon from '@/components/app-logo-icon';

export default function AppLogo() {
    return (
        <div className="flex items-center gap-2 overflow-hidden">
            <AppLogoIcon className="size-8 shrink-0" />
            <span className="truncate text-lg font-semibold group-data-[collapsible=icon]:hidden">
                <span style={{ color: '#E38B2C' }}>NeoLabs</span>{' '}
                <span style={{ color: '#C96A1A' }}>AI</span>
            </span>
        </div>
    );
}
