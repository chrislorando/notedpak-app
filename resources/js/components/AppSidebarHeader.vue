<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { Search, X } from 'lucide-vue-next';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import Button from './ui/button/Button.vue';
import Input from './ui/input/Input.vue';

const props = withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const showSearch = ref(false);
const page = usePage();

const search = ref<string>(String(page.props.search ?? ''));
const searchInput = ref<any>(null);

const handleResize = () => {
    if (window.innerWidth < 768) {
        showSearch.value = false;
    }
};

onMounted(() => {
    // window.addEventListener('resize', handleResize);
    // handleResize();
    searchInput.value?.focus();
});

onBeforeUnmount(() => {
    // window.removeEventListener('resize', handleResize);
});

watch(search, (val) => {
    console.log('SEARCH', val);
    router.get(
        route('tasks.search'),
        {
            q: String(val),
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
});

const goToSearch = () => {
    showSearch.value = true;
    searchInput.value?.focus();
    router.get(
        route('tasks.search'),
        {
            q: String(search.value),
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
};

const exitSearch = () => {
    showSearch.value = false;
    search.value = '';
};
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <!-- <div class="flex items-center gap-2">
            <SidebarTrigger :color="breadcrumbs?.[breadcrumbs.length - 1]?.color" class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div> -->
        <div :class="['w-full items-center gap-2', showSearch ? '' : 'flex justify-between']">
            <div class="flex items-center gap-2" v-if="!showSearch">
                <SidebarTrigger :color="breadcrumbs?.[breadcrumbs.length - 1]?.color" class="-ml-1" />
                <template v-if="breadcrumbs && breadcrumbs.length > 0">
                    <Breadcrumbs :breadcrumbs="breadcrumbs" />
                </template>
            </div>
            <!-- <div class="flex justify-end">
                <Input class="w-48 border border-gray-300" />
            </div> -->

            <div class="relative ms-auto flex items-center">
                <!-- Desktop -->
                <div class="hidden w-60 items-center md:flex">
                    <Input
                        id="search"
                        type="text"
                        placeholder="Search..."
                        class="w-full border border-gray-500 pl-10"
                        autocomplete="off"
                        v-model="search"
                        ref="searchInput"
                    />
                    <span class="absolute inset-y-0 start-0 flex items-center px-2">
                        <Search class="size-5 text-muted-foreground" />
                    </span>
                </div>

                <!-- Mobile -->
                <div class="flex w-full items-center md:hidden">
                    <Button v-if="!showSearch" variant="ghost" @click="goToSearch">
                        <Search class="size-5 text-muted-foreground" />
                    </Button>

                    <div v-else class="relative flex w-full items-center">
                        <Input
                            id="search"
                            type="text"
                            placeholder="Search..."
                            class="w-full border border-gray-500 pr-10"
                            autocomplete="off"
                            v-model="search"
                            ref="searchInput"
                        />
                        <span class="absolute inset-y-0 end-0 flex items-center px-2">
                            <Button variant="ghost" @click="exitSearch">
                                <X class="size-6 text-muted-foreground" />
                            </Button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>
