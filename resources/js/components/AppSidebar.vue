<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { useTranslations } from '@/composables/useTranslations';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Trash2 } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const { __ } = useTranslations();

const mainNavItems: NavItem[] = [
    {
        title: __('common.nav.dashboard'),
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: __('common.nav.clients'),
        href: '/clients',
        icon: Folder,
    },
    {
        title: __('common.nav.trash'),
        href: '/trash',
        icon: Trash2,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: __('common.nav.github'),
        href: 'https://github.com/melasistema/clients-melasistema',
        icon: Folder,
    },
    {
        title: __('common.nav.documentation'),
        href: 'https://github.com/melasistema/clients-melasistema#readme',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
