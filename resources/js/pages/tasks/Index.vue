<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ChevronsUpDown, Star } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';

const breadcrumbs = ref<BreadcrumbItem[]>([
    {
        title: 'Tasks',
        href: 'tasks',
    },
]);

onMounted(() => {
    console.log('ADD OVERFLOW 1');
    window.scrollTo(0, 0);
    document.body.classList.add('overflow-hidden');
});

// Define props with TypeScript type
const props = defineProps<{ project: any; draftTasks: any; completedTasks: any }>();

const initProject = ref<any>(props.project);

const form = useForm({
    description: '',
    project_uuid: '',
});

watch(
    () => props.project,
    (project: any) => {
        if (project) {
            breadcrumbs.value = [
                {
                    title: project.name,
                    href: `/projects/${project.uuid}`,
                    color: project.color,
                },
            ];

            initProject.value = project;
            form.project_uuid = initProject.value?.uuid;
            form.reset('description');

            console.log(project);
        }
    },
    { immediate: true },
);

const isDisabled = computed(() => form.description.trim() === '' || form.processing);

function add() {
    form.post('/tasks', {
        preserveScroll: true,
        onSuccess: function () {
            form.reset('description');
        },
    });
}

function complete(uuid: string) {
    router.patch(
        route('tasks.complete', initProject.value?.uuid),
        {
            uuid: uuid,
        },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: function () {},
        },
    );
}

function bookmark(uuid: string) {
    router.patch(
        route('tasks.bookmark', initProject.value?.uuid),
        {
            uuid: uuid,
        },
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
}
</script>

<template>
    <Head :title="initProject.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full w-full flex-1 flex-col gap-1 rounded-xl p-4" style="width: 100%">
            <div class="relative h-screen overflow-hidden">
                <div
                    class="absolute top-0 right-0 left-0 flex items-center rounded-sm border px-4 py-3 text-sm font-semibold shadow backdrop-blur-sm dark:bg-zinc-900"
                >
                    <form @submit.prevent="add" class="flex w-full items-center space-x-2 rounded-md">
                        <Input
                            type="text"
                            id="description"
                            name="description"
                            placeholder="Add a task"
                            class="light:text-zinc-100 flex-1 border-none px-4 py-5"
                            v-model="form.description"
                        />
                        <Button type="submit" class="bg-primary py-5" :style="{ background: project.color }" variant="default" :disabled="isDisabled">
                            Add
                        </Button>
                    </form>
                </div>
                <div class="flex h-screen flex-col divide-y divide-gray-200 overflow-auto">
                    <div class="flex flex-col gap-2 pt-20 pb-24">
                        <ul>
                            <li
                                v-bind:key="item.id"
                                v-for="item in draftTasks"
                                class="mb-1 flex items-center justify-between rounded-sm border p-5 shadow dark:bg-zinc-900"
                            >
                                <div class="flex items-start space-x-2">
                                    <Checkbox
                                        @update:model-value="complete(item.uuid)"
                                        class="rounded-2xl border-foreground"
                                        :style="{ borderColor: project.color }"
                                    />
                                    <label class="text-sm leading-4 font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                        {{ item.description }}
                                    </label>
                                </div>

                                <button type="button" class="text-gray-400 hover:text-yellow-500" @click="bookmark(item.uuid)">
                                    <Star :size="18" :fill="item.is_important ? '#facc15' : ''" />
                                </button>
                            </li>
                        </ul>

                        <Collapsible :default-open="completedTasks.length > 0" :hidden="completedTasks.length === 0" class="mt-1 w-full space-y-3">
                            <div class="flex w-35 items-center space-x-0">
                                <CollapsibleTrigger asChild>
                                    <Button variant="default" size="sm" :style="{ background: project.color }">
                                        <h4 class="text-sm font-semibold">Completed {{ completedTasks.length }}</h4>
                                        <ChevronsUpDown class="h-4 w-4" />
                                        <span class="sr-only">Toggle</span>
                                    </Button>
                                </CollapsibleTrigger>
                            </div>
                            <CollapsibleContent class="space-y-2">
                                <ul>
                                    <li
                                        v-bind:key="item.id"
                                        v-for="item in completedTasks"
                                        class="mb-1 flex items-center justify-between rounded-sm border p-5 shadow dark:bg-zinc-900"
                                    >
                                        <div class="flex items-start space-x-3">
                                            <Checkbox
                                                class="rounded-2xl"
                                                :id="`task-${item.id}`"
                                                :default-value="true"
                                                @update:model-value="complete(item.uuid)"
                                                :style="{ borderColor: project.color, background: project.color }"
                                            />
                                            <label
                                                :for="`task-${item.id}`"
                                                class="text-sm leading-4 font-medium line-through peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                            >
                                                {{ item.description }}
                                            </label>
                                        </div>

                                        <button type="button" class="text-gray-400 hover:text-yellow-500" @click="bookmark(item.uuid)">
                                            <Star :size="18" :fill="item.is_important ? '#facc15' : ''" />
                                        </button>
                                    </li>
                                </ul>
                            </CollapsibleContent>
                        </Collapsible>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
