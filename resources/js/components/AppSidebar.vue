<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, List } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import AppLogo from './AppLogo.vue';
import NavTodo from './NavTodo.vue';
import ProjectModalForm from './ProjectModalForm.vue';

// const props = defineProps({})
const page = usePage();

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    // {
    //     title: 'Important',
    //     href: '/important',
    //     icon: Star,
    // },
    // {
    //     title: 'Assigned to me',
    //     href: '/dashboard',
    //     icon: UserCircle,
    // },
];

// const footerNavItems: NavItem[] = [
//     {
//         title: 'Github Repo',
//         href: 'https://github.com/laravel/vue-starter-kit',
//         icon: Folder,
//     },
//     {
//         title: 'Documentation',
//         href: 'https://laravel.com/docs/starter-kits#vue',
//         icon: BookOpen,
//     },
// ];

const todoNavItems = ref<NavItem[]>([]);

watch(
    () => page.props.projects,
    (newVal) => {
        const lists = Array.isArray(newVal) ? newVal : [];

        todoNavItems.value = lists.map((list) => ({
            uuid: list.uuid,
            title: list.name,
            href: `/tasks/${list.uuid}`,
            icon: List,
            count: list.draft_tasks_count,
            color: list.color,
        }));
    },
    { immediate: true },
);
</script>

<template>
    <Sidebar collapsible="offcanvas" variant="sidebar">
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
            <NavMain title="Platform" :items="mainNavItems" />
            <NavTodo title="To do list" :items="todoNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <!-- <NavFooter :items="footerNavItems" /> -->
            <ProjectModalForm :is-new-record="true" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
