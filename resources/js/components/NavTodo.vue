<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { List } from 'lucide-vue-next';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from './ui/tooltip';

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
                    <Link :href="item.href" class="flex items-center justify-between text-[13px]" preserveState :preserveScroll="true">
                        <TooltipProvider>
                            <Tooltip>
                                <TooltipTrigger class="flex min-w-0 items-center gap-1.5">
                                    <!-- <component :is="item.icon"  /> -->
                                    <List :size="15" class="shrink-0" :style="item.color ? { color: item.color } : undefined" />
                                    <span class="truncate">{{ item.title }}</span>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{{ item.title }}</p>
                                </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                        <Badge v-if="(item.count ?? 0) > 0" className="rounded-sm bg-primary text-black px-1 py-0.5 text-[10px]">{{
                            item.count ?? 0
                        }}</Badge>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
