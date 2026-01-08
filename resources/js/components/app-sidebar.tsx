import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { PageProps, type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { FileText, HomeIcon, LayoutGrid, Package, ShoppingCart, Users } from 'lucide-react';
import { useMemo } from 'react';
import { AppLogo } from './app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Entreprises',
        href: '/companies',
        icon: HomeIcon,
        roles: ['super_admin'],
    },
    {
        title: 'Clients',
        href: '/clients',
        icon: Users,
    },
    {
        title: 'Catalogues',
        href: '/catalogues',
        icon: Package,
    },
    {
        title: 'Articles',
        href: '/articles',
        icon: Package,
    },
    {
        title: 'Entrées / Sorties',
        href: '/mouvements',
        icon: ShoppingCart,
        roles: ['super_admin', 'admin_company'],
    },
    {
        title: 'Reçus / Factures',
        href: '/quotes',
        icon: FileText,
    },
    {
        title: 'Utilisateurs',
        href: '/users',
        icon: Users,
        roles: ['super_admin', 'admin_company'],
    },
];

export function AppSidebar() {
    const { auth } = usePage<PageProps>().props;
    const company = auth?.user?.company;
    const userRole = auth?.user?.user_role;
    const logo = company && company?.company_logo ? `/storage/${company?.company_logo}` : '/facture-pro.png';

    const filteredNavItems = useMemo(() => {
        return mainNavItems.filter((item) => {
            if (!item.roles || item.roles.length === 0) {
                return true;
            }
            return item.roles.includes(userRole || '');
        });
    }, [userRole]);

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/company/profile" prefetch>
                                <AppLogo logo={logo} title={company?.company_name} />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={filteredNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
