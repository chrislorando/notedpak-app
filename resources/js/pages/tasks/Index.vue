<script setup lang="ts">
import DatePicker from '@/components/DatePicker.vue';
import ProjectModalForm from '@/components/ProjectModalForm.vue';
import { AlertDialogAction } from '@/components/ui/alert-dialog';
import AlertDialog from '@/components/ui/alert-dialog/AlertDialog.vue';
import AlertDialogCancel from '@/components/ui/alert-dialog/AlertDialogCancel.vue';
import AlertDialogContent from '@/components/ui/alert-dialog/AlertDialogContent.vue';
import AlertDialogDescription from '@/components/ui/alert-dialog/AlertDialogDescription.vue';
import AlertDialogFooter from '@/components/ui/alert-dialog/AlertDialogFooter.vue';
import AlertDialogHeader from '@/components/ui/alert-dialog/AlertDialogHeader.vue';
import AlertDialogTitle from '@/components/ui/alert-dialog/AlertDialogTitle.vue';
import AlertDialogTrigger from '@/components/ui/alert-dialog/AlertDialogTrigger.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import DropdownMenu from '@/components/ui/dropdown-menu/DropdownMenu.vue';
import DropdownMenuContent from '@/components/ui/dropdown-menu/DropdownMenuContent.vue';
import DropdownMenuLabel from '@/components/ui/dropdown-menu/DropdownMenuLabel.vue';
import DropdownMenuRadioGroup from '@/components/ui/dropdown-menu/DropdownMenuRadioGroup.vue';
import DropdownMenuRadioItem from '@/components/ui/dropdown-menu/DropdownMenuRadioItem.vue';
import DropdownMenuSeparator from '@/components/ui/dropdown-menu/DropdownMenuSeparator.vue';
import DropdownMenuTrigger from '@/components/ui/dropdown-menu/DropdownMenuTrigger.vue';
import { Input } from '@/components/ui/input';
import ScrollArea from '@/components/ui/scroll-area/ScrollArea.vue';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import Sheet from '@/components/ui/sheet/Sheet.vue';
import SheetContent from '@/components/ui/sheet/SheetContent.vue';
import SheetFooter from '@/components/ui/sheet/SheetFooter.vue';
import SheetHeader from '@/components/ui/sheet/SheetHeader.vue';
import SheetTitle from '@/components/ui/sheet/SheetTitle.vue';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowUpDown, CalendarDays, CalendarPlus, ChevronsUpDown, Dot, File, Star, Tag, Trash2 } from 'lucide-vue-next';
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
const activeTask = ref();
const openTaskSheet = ref(false);
const sort = ref('description');

const form = useForm({
    description: '',
    note: '',
    due_date: '',
    category: '',
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

watch(openTaskSheet, (val) => {
    if (val === false) {
        form.reset('description');
        form.description = '';
    }
});

const isDisabled = computed(() => form.description.trim() === '' || form.processing);

function addTask() {
    form.post('/tasks', {
        preserveScroll: true,
        onSuccess: function () {
            form.reset('description');
        },
    });
}

function editTask(uuid: string) {
    form.put(route('tasks.update', uuid), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            showSheet(uuid);
        },
    });
}

// const debouncedEdit = useDebounceFn((uuid: string) => {
//     editTask(uuid);
// }, 500);

function completeTask(uuid: string) {
    router.patch(
        route('tasks.complete', uuid),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: function (result) {
                console.log('COMPLETED', result);
                if (openTaskSheet.value) {
                    setActiveTask(uuid);
                }
            },
        },
    );
}

function bookmarkTask(uuid: string) {
    router.patch(
        route('tasks.bookmark', uuid),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: function (result) {
                console.log('BOOKMARKED', result);
                if (openTaskSheet.value) {
                    setActiveTask(uuid);
                }
            },
        },
    );
}

function deleteTask(uuid: string) {
    router.delete(route('tasks.destroy', uuid), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: function (result) {
            console.log('DESTROYED', result);
            if (openTaskSheet) {
                activeTask.value = [];
                openTaskSheet.value = false;
            }
        },
    });
}

function deleteList(uuid: string) {
    router.delete(route('projects.destroy', uuid), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: function (result) {
            console.log('DESTROYED', result);
            // if (openTaskSheet) {
            //     activeTask.value = [];
            //     openTaskSheet.value = false;
            // }
        },
    });
}

function setActiveTask(uuid: string) {
    const task = [...props.draftTasks, ...props.completedTasks].find((t) => t.uuid === uuid);
    activeTask.value = task;
    form.description = task.description || '';
}

function showSheet(uuid: string) {
    openTaskSheet.value = true;
    setActiveTask(uuid);
}

function submitSort() {
    console.log('SORT', sort.value);
    router.get(
        route('tasks.show', initProject.value?.uuid),
        {
            sort: sort.value,
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
        <div class="flex h-full w-full flex-1 flex-col gap-1 rounded-xl p-5" style="width: 100%">
            <div class="flex w-full items-start justify-end">
                <ProjectModalForm :is-new-record="false" :data="initProject" />
                <div class="space-y-3">
                    <DropdownMenu>
                        <DropdownMenuTrigger
                            class="inline-flex h-9 shrink-0 items-center justify-center gap-2 rounded-md px-4 py-2 text-base font-medium whitespace-nowrap transition-all outline-none hover:bg-accent hover:text-accent-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:pointer-events-none disabled:opacity-50 has-[>svg]:px-3 aria-invalid:border-destructive aria-invalid:ring-destructive/20 dark:hover:bg-accent/50 dark:aria-invalid:ring-destructive/40 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4"
                        >
                            <ArrowUpDown />
                            <span>Sort</span>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent>
                            <DropdownMenuLabel>Sort by</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuRadioGroup v-model="sort" @update:modelValue="submitSort">
                                <DropdownMenuRadioItem value="is_important"><Star /> Importance</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="due_date"><CalendarDays /> Due date</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="description"><ArrowUpDown /> Alphabetically</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem value="created_at"><CalendarPlus /> Creation date</DropdownMenuRadioItem>
                            </DropdownMenuRadioGroup>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <AlertDialog>
                        <AlertDialogTrigger as-child>
                            <Button variant="ghost" class="text-red-500"> <Trash2 /> Remove list </Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>"{{ initProject.name }}" will be permanently deleted.</AlertDialogTitle>
                                <AlertDialogDescription> This won't be able to undo this action. </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                <AlertDialogAction @click.stop="deleteList(initProject.uuid)" class="bg-[var(--destructive)] text-white"
                                    >Continue</AlertDialogAction
                                >
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </div>
            </div>

            <div class="relative">
                <div
                    class="absolute top-0 right-0 left-0 flex flex-col items-center rounded-sm border px-3 py-3 text-sm font-semibold shadow backdrop-blur-sm dark:bg-zinc-900"
                >
                    <form @submit.prevent="addTask" class="flex w-full items-center space-x-2 rounded-md">
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
            </div>

            <ScrollArea class="mt-20 h-[calc(100vh-200px)] w-[calc(100%+20px)] rounded-md border-0 pb-10">
                <div class="flex w-[calc(100%-20px)] flex-col gap-2">
                    <ul>
                        <li
                            @click.stop="showSheet(item.uuid)"
                            v-bind:key="item.id"
                            v-for="item in draftTasks"
                            class="mb-1 flex items-center justify-between rounded-sm border p-5 shadow dark:bg-zinc-900"
                        >
                            <div class="flex items-start space-x-3">
                                <Checkbox
                                    @click.stop
                                    @update:model-value="completeTask(item.uuid)"
                                    class="rounded-2xl border-foreground"
                                    :style="{ borderColor: project.color }"
                                />
                                <label class="text-sm leading-4 font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                    {{ item.description }}
                                </label>
                            </div>

                            <button type="button" class="text-gray-400 hover:text-yellow-500" @click.stop="bookmarkTask(item.uuid)">
                                <Star :size="18" :fill="item.is_important ? project.color : ''" />
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
                                    @click.stop="showSheet(item.uuid)"
                                    v-bind:key="item.id"
                                    v-for="item in completedTasks"
                                    class="mb-1 flex items-center justify-between rounded-sm border p-5 shadow dark:bg-zinc-900"
                                >
                                    <div class="flex items-start space-x-3">
                                        <Checkbox
                                            @click.stop
                                            @update:model-value="completeTask(item.uuid)"
                                            class="rounded-2xl"
                                            :id="`task-${item.id}`"
                                            :default-value="true"
                                            :style="{ borderColor: project.color, background: project.color }"
                                        />
                                        <label
                                            class="text-sm leading-4 font-medium line-through peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                        >
                                            {{ item.description }}
                                        </label>
                                    </div>

                                    <button type="button" class="text-gray-400 hover:text-yellow-500" @click.stop="bookmarkTask(item.uuid)">
                                        <Star :size="18" :fill="item.is_important ? project.color : ''" />
                                    </button>
                                </li>
                            </ul>
                        </CollapsibleContent>
                    </Collapsible>
                </div>
            </ScrollArea>
        </div>
    </AppLayout>

    <Sheet v-model:open="openTaskSheet">
        <SheetContent class="w-full max-w-full gap-2">
            <SheetHeader>
                <SheetTitle>Edit task</SheetTitle>
            </SheetHeader>

            <div class="flex w-full items-center justify-between rounded-sm p-4 shadow dark:bg-zinc-900">
                <Checkbox
                    @click.stop
                    @update:model-value="completeTask(activeTask.uuid)"
                    class="me-4 items-center rounded-2xl border-foreground"
                    :default-value="activeTask.is_completed ? true : false"
                    :style="{ borderColor: project.color, background: activeTask.is_completed ? project.color : '' }"
                />

                <div class="flex-1">
                    <Textarea
                        id="description"
                        autocomplete="off"
                        class="min-h-[0px] w-full resize-none border-0 line-through"
                        name="description"
                        v-model="form.description"
                        @blur="editTask(activeTask.uuid)"
                        @keydown.enter.prevent="editTask(activeTask.uuid)"
                        autoComplete="off"
                    />
                </div>

                <button type="button" class="ms-4 text-gray-400 hover:text-yellow-500" @click.stop="bookmarkTask(activeTask.uuid)">
                    <Star :size="18" :fill="activeTask.is_important ? project.color : ''" />
                </button>
            </div>

            <div class="flex w-full items-center justify-between rounded-sm p-2 shadow dark:bg-zinc-900">
                <DatePicker placeholder="Add due date" id="due_date" class="w-full border-0" v-model="form.due_date" />
            </div>

            <div class="flex w-full items-center justify-between rounded-sm p-2 shadow dark:bg-zinc-900">
                <Select :multiple="true" v-model="form.category">
                    <SelectTrigger class="w-full border-0">
                        <div class="flex items-center gap-2">
                            <Tag class="me-2 h-4 w-4" />
                            <SelectValue placeholder="Select a category" />
                        </div>
                    </SelectTrigger>
                    <SelectContent class="dark:bg-zinc-900">
                        <SelectGroup>
                            <!-- <SelectLabel>Fruits</SelectLabel> -->
                            <SelectItem value="blue" class="text-blue-500"> <Dot /> Blue </SelectItem>
                            <SelectItem value="green" class="text-green-500"> <Dot /> Green </SelectItem>
                            <SelectItem value="orange" class="text-orange-500"> <Dot /> Orange </SelectItem>
                            <SelectItem value="red" class="text-red-500"> <Dot /> Red </SelectItem>
                            <SelectItem value="yellow" class="text-yellow-500"> <Dot /> Yellow </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </div>

            <div class="flex w-full items-center justify-between rounded-sm p-2 shadow dark:bg-zinc-900">
                <div class="relative w-full max-w-sm items-center">
                    <Input
                        type="file"
                        id="file"
                        class="w-full resize-none border-0 pl-12"
                        name="file"
                        v-model="form.note"
                        @keyup.prevent="editTask(activeTask.uuid)"
                        autoComplete="off"
                        placeholder="Add file"
                    />
                    <span class="absolute inset-y-0 start-0 flex items-center justify-center px-2">
                        <File class="ms-2 size-4 text-muted-foreground" />
                    </span>
                </div>
            </div>

            <div class="mb-1 flex w-full items-center justify-between rounded-sm p-2 shadow dark:bg-zinc-900">
                <Textarea
                    id="note"
                    class="w-full resize-none border-0"
                    name="description"
                    v-model="form.note"
                    @keyup.prevent="editTask(activeTask.uuid)"
                    autoComplete="off"
                    placeholder="Add note"
                />
            </div>

            <SheetFooter class="text-center">
                <small v-if="activeTask.completed_at"> Completed on {{ activeTask.completed_at }} </small>
                <small v-else> Created on {{ activeTask.created_at }} </small>

                <AlertDialog>
                    <AlertDialogTrigger as-child>
                        <Button variant="destructive"> <Trash2 /> Delete </Button>
                    </AlertDialogTrigger>
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>"{{ activeTask.description }}" will be permanently deleted.</AlertDialogTitle>
                            <AlertDialogDescription> This won't be able to undo this action. </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                            <AlertDialogAction @click.stop="deleteTask(activeTask.uuid)" class="bg-[var(--destructive)] text-white"
                                >Continue</AlertDialogAction
                            >
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
                <!-- <SheetClose as-child>
                    <Button type="button" @click.stop="deleteTask(activeTask.uuid)" variant="destructive"> <Trash2 /> Delete </Button>
                </SheetClose> -->
            </SheetFooter>
        </SheetContent>
    </Sheet>
</template>
