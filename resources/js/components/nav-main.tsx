import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';

import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import type { IsCurrentUrlFn } from '@/hooks/use-current-url';
import { useCurrentUrl } from '@/hooks/use-current-url';
import type { NavItem } from '@/types';

export function NavMain({
    items = [],
    label = 'Platform',
}: {
    items: NavItem[];
    label?: string;
}) {
    const { isCurrentUrl } = useCurrentUrl();

    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarGroupLabel className="text-xs font-medium tracking-wider text-muted-foreground/70 uppercase">
                {label}
            </SidebarGroupLabel>
            <SidebarMenu>
                {items.map((item) =>
                    item.children && item.children.length > 0 ? (
                        <CollapsibleNavItem
                            key={item.title}
                            item={item}
                            isCurrentUrl={isCurrentUrl}
                        />
                    ) : (
                        <SidebarMenuItem key={item.title}>
                            <SidebarMenuButton
                                asChild
                                isActive={isCurrentUrl(item.href)}
                                tooltip={{ children: item.title }}
                            >
                                <Link href={item.href} prefetch>
                                    {item.icon && <item.icon />}
                                    <span>{item.title}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    ),
                )}
            </SidebarMenu>
        </SidebarGroup>
    );
}

function CollapsibleNavItem({
    item,
    isCurrentUrl,
}: {
    item: NavItem;
    isCurrentUrl: IsCurrentUrlFn;
}) {
    const isChildActive = item.children?.some((child) =>
        isCurrentUrl(child.href),
    );

    return (
        <Collapsible
            asChild
            defaultOpen={isChildActive}
            className="group/collapsible"
        >
            <SidebarMenuItem>
                <CollapsibleTrigger asChild>
                    <SidebarMenuButton
                        tooltip={{ children: item.title }}
                        isActive={isChildActive}
                    >
                        {item.icon && <item.icon />}
                        <span>{item.title}</span>
                        <ChevronRight className="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                    </SidebarMenuButton>
                </CollapsibleTrigger>
                <CollapsibleContent>
                    <SidebarMenuSub>
                        {item.children?.map((child) => (
                            <SidebarMenuSubItem key={child.title}>
                                <SidebarMenuSubButton
                                    asChild
                                    isActive={isCurrentUrl(child.href)}
                                >
                                    <Link href={child.href} prefetch>
                                        <span>{child.title}</span>
                                    </Link>
                                </SidebarMenuSubButton>
                            </SidebarMenuSubItem>
                        ))}
                    </SidebarMenuSub>
                </CollapsibleContent>
            </SidebarMenuItem>
        </Collapsible>
    );
}
