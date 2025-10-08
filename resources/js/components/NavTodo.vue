<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuBadge,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import draggable from 'vuedraggable';

const props = defineProps<{
    title: string | null;
    items: NavItem[];
}>();

const page = usePage();
const { toggleSidebar, isMobile } = useSidebar();

const emit = defineEmits<{
    (e: 'update:items', value: NavItem[]): void;
}>();

const localItems = ref([...props.items]);

watch(
    () => props.items,
    (val) => {
        localItems.value = [...val];
    },
);

watch(localItems, (val) => {
    emit('update:items', val);
});

const onReorder = () => {
    const ordered = localItems.value.map((item, index) => ({
        id: item.id,
        position: index + 1,
    }));

    router.patch(
        '/projects/reorder',
        { ordered },
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
};
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>{{ title }}</SidebarGroupLabel>
        <SidebarMenu>
            <draggable v-model="localItems" item-key="id" @end="onReorder">
                <template #item="{ element: item }">
                    <SidebarMenuItem>
                        <SidebarMenuButton as-child :is-active="item.href === page.url" :tooltip="item.title">
                            <Link :href="item.href" preserveState :preserveScroll="true" @click="isMobile ? toggleSidebar() : null" replace>
                                <component :is="item.icon" :style="item.color ? { color: item.color } : undefined" />
                                <span>{{ item.title }}</span>
                            </Link>
                        </SidebarMenuButton>

                        <SidebarMenuBadge class="bg-zinc-100 dark:bg-zinc-900" v-if="item.count ?? 0">
                            {{ item.count ?? 0 }}
                        </SidebarMenuBadge>
                    </SidebarMenuItem>
                </template>
            </draggable>
        </SidebarMenu>
    </SidebarGroup>
</template>
