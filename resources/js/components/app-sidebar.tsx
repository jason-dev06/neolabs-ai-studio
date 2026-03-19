import { Link } from '@inertiajs/react';
import {
    FileText,
    LayoutGrid,
    MessageSquare,
    Mic,
    Paintbrush,
    Video,
    Wand2,
} from 'lucide-react';

import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuItem,
    SidebarSeparator,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as imageEditorIndex } from '@/routes/image-editor';
import { index as imageGeneratorIndex } from '@/routes/image-generator';
import { index as videoEditorIndex } from '@/routes/video-editor';
import { index as videoGeneratorIndex } from '@/routes/video-generator';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

const aiToolNavItems: NavItem[] = [
    {
        title: 'Image Generator',
        href: imageGeneratorIndex(),
        icon: Wand2,
    },
    {
        title: 'Image Editor',
        href: imageEditorIndex(),
        icon: Paintbrush,
    },
    {
        title: 'Chat',
        href: '#',
        icon: MessageSquare,
    },
    {
        title: 'Documents',
        href: '#',
        icon: FileText,
    },
    {
        title: 'Video',
        href: videoGeneratorIndex(),
        icon: Video,
        children: [
            { title: 'Generator', href: videoGeneratorIndex() },
            { title: 'Editor', href: videoEditorIndex() },
        ],
    },
    {
        title: 'Transcribe',
        href: '#',
        icon: Mic,
    },
];

const footerNavItems: NavItem[] = [];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <Link
                            href={dashboard()}
                            prefetch
                            className="flex items-center p-2"
                        >
                            <AppLogo />
                        </Link>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent className="overflow-x-hidden">
                <NavMain items={mainNavItems} label="Overview" />
                <SidebarSeparator className="mx-4" />
                <NavMain items={aiToolNavItems} label="AI Tools" />
            </SidebarContent>

            <SidebarFooter>
                {footerNavItems.length > 0 && (
                    <NavFooter items={footerNavItems} className="mt-auto" />
                )}
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
