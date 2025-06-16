<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuBadge, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

defineProps<{
    title: string | null;
    items: NavItem[];
}>();

const page = usePage();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>{{ title }}</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton as-child :is-active="item.href === page.url" :tooltip="item.title">
                    <Link :href="item.href" preserveState :preserveScroll="true">
                        <component :is="item.icon" :style="item.color ? { color: item.color } : undefined" />
                        <!-- <span>📊</span> -->
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>

                <SidebarMenuBadge class="bg-zinc-100 dark:bg-zinc-900" v-if="item.count ?? 0">{{ item.count ?? 0 }}</SidebarMenuBadge>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
