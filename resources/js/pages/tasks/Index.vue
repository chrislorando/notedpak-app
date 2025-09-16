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
import {
    Combobox,
    ComboboxAnchor,
    ComboboxEmpty,
    ComboboxGroup,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxList,
    ComboboxTrigger,
} from '@/components/ui/combobox';

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
import SheetClose from '@/components/ui/sheet/SheetClose.vue';
import SheetContent from '@/components/ui/sheet/SheetContent.vue';
import SheetFooter from '@/components/ui/sheet/SheetFooter.vue';
import SheetHeader from '@/components/ui/sheet/SheetHeader.vue';
import SheetTitle from '@/components/ui/sheet/SheetTitle.vue';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { customFormatDate, dateValueToString, stringToDateValue } from '@/lib/date';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    ArrowUpDown,
    CalendarDays,
    CalendarDaysIcon,
    CalendarPlus,
    Check,
    ChevronsUpDown,
    Circle,
    CircleIcon,
    Copy,
    File,
    List,
    ListEndIcon,
    PanelRightClose,
    Star,
    StickyNote,
    Tag,
    Trash2,
} from 'lucide-vue-next';
import { ComboboxContent, DateValue } from 'reka-ui';
import { computed, onMounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

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
const props = defineProps<{ project: any; draftTasks: any; completedTasks: any; categoryOptions: any; listOptions: any }>();

const initProject = ref<any>(props.project);
const activeTask = ref();
const openTaskSheet = ref(false);
const sort = ref('description');
const lists = ref<any>(props.listOptions);
const categories = ref<any>(props.categoryOptions);
const selectedList = ref<(typeof props.listOptions)[0] | undefined>();
const selectedDueDate = ref<DateValue>();

const form = useForm({
    description: '',
    note: '',
    due_date: null as string | null,
    categories: '',
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
    form.due_date = selectedDueDate.value ? dateValueToString(selectedDueDate.value) : null;
    form.categories = JSON.stringify(form.categories);
    console.log('categories', form.categories);
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
    form.note = task.note || '';
    form.categories = task.categories ? JSON.parse(task.categories).map((cat: any) => cat.value ?? cat) : [];
    if (task.due_date) {
        selectedDueDate.value = stringToDateValue(task.due_date);
    }
}

function showSheet(uuid: string) {
    openTaskSheet.value = true;
    setActiveTask(uuid);
}

function hideSheet(uuid: string) {
    editTask(uuid);
    openTaskSheet.value = true;
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

const searchLists = async (q: string) => {
    if (!q) {
        lists.value = props.listOptions;
        return;
    }

    try {
        const res = await fetch(`/tasks/search-list-options?search=${q}`);
        lists.value = await res.json();
    } catch (e) {
        console.error('Search error:', e);
    }
};

const copyList = () => {
    console.log('COPY', props.project);
    router.patch(
        route('projects.copy', props.project.uuid),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: function (result) {
                selectedList.value = '';
                lists.value = props.listOptions;
                toast.success('List has been copied', {
                    description: Date().toString(),
                    icon: Check,
                    duration: 5000,
                });
            },
        },
    );
};

const copyTask = (taskId: string) => {
    console.log('COPY', selectedList.value, taskId);
    router.patch(
        route('tasks.copy', taskId),
        {
            project_id: selectedList.value,
        },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: function (result) {
                selectedList.value = '';
                lists.value = props.listOptions;
                toast.success('Task has been copied', {
                    description: Date().toString(),
                    icon: Check,
                    duration: 5000,
                });
            },
        },
    );
};

const moveTask = (taskId: string) => {
    console.log('MOVE', selectedList.value, taskId);
    router.patch(
        route('tasks.move', taskId),
        {
            project_id: selectedList.value,
        },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: function (result) {
                selectedList.value = '';
                lists.value = props.listOptions;
                toast.success('Task has been moved', {
                    description: Date().toString(),
                    icon: Check,
                    duration: 5000,
                });
            },
        },
    );
};
</script>

<template>
    <Head :title="initProject.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full w-full flex-1 flex-col gap-1 rounded-xl p-5" style="width: 100%">
            <div class="flex w-full items-start justify-end">
                <ProjectModalForm :is-new-record="false" :data="initProject" />
                <Button variant="ghost" @click="copyList" class="cursor-pointer">
                    <Copy /> <span class="hidden md:inline-block">Duplicate</span>
                </Button>
                <div class="space-y-3">
                    <DropdownMenu>
                        <DropdownMenuTrigger
                            class="inline-flex h-9 shrink-0 cursor-pointer items-center justify-center gap-2 rounded-md px-4 py-2 text-base font-medium whitespace-nowrap transition-all outline-none hover:bg-accent hover:text-accent-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:pointer-events-none disabled:opacity-50 has-[>svg]:px-3 aria-invalid:border-destructive aria-invalid:ring-destructive/20 dark:hover:bg-accent/50 dark:aria-invalid:ring-destructive/40 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4"
                        >
                            <ArrowUpDown />
                            <span class="hidden md:inline-block">Sort</span>
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
                            <Button variant="ghost" class="cursor-pointer text-[var(--destructive)]">
                                <Trash2 /> <span class="hidden md:inline-block">Remove list</span>
                            </Button>
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
                            autocomplete="off"
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
                                    <div class="mt-1 flex flex-wrap gap-2 text-xs">
                                        <!-- Due Date -->
                                        <span v-if="item.due_date" class="flex items-center gap-1 text-gray-400">
                                            <CalendarDaysIcon class="h-3.5 w-3.5" />
                                            {{ customFormatDate(item.due_date) }}
                                        </span>

                                        <!-- Note -->
                                        <span v-if="item.note" class="flex items-center gap-1 text-gray-400">
                                            <StickyNote class="h-3.5 w-3.5" />
                                            Note
                                        </span>

                                        <!-- Categories - FIXED -->
                                        <template v-if="item.categories && item.categories.length">
                                            <span
                                                v-for="(cat, index) in JSON.parse(item.categories)"
                                                :key="index"
                                                :class="`flex items-center gap-1 text-xs text-${cat}-500 capitalize`"
                                            >
                                                <Circle class="h-2.5 w-2.5" />
                                                {{ cat }} category
                                            </span>
                                        </template>
                                    </div>
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
                                        <label class="text-sm leading-4 font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                            <span class="line-through">{{ item.description }}</span>

                                            <div class="mt-1 flex flex-wrap gap-2 text-xs">
                                                <!-- Due Date -->
                                                <span v-if="item.due_date" class="flex items-center gap-1 text-gray-400">
                                                    <CalendarDaysIcon class="h-3.5 w-3.5" />
                                                    {{ customFormatDate(item.due_date) }}
                                                </span>

                                                <!-- Note -->
                                                <span v-if="item.note" class="flex items-center gap-1 text-gray-400">
                                                    <StickyNote class="h-3.5 w-3.5" />
                                                    Note
                                                </span>

                                                <!-- Categories - FIXED -->
                                                <template v-if="item.categories && item.categories.length">
                                                    <span
                                                        v-for="(cat, index) in JSON.parse(item.categories)"
                                                        :key="index"
                                                        :class="`flex items-center gap-1 text-xs text-${cat}-500 capitalize`"
                                                    >
                                                        <Circle class="h-2.5 w-2.5" />
                                                        {{ cat }} category
                                                    </span>
                                                </template>
                                            </div>
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

    <Sheet v-model:open="openTaskSheet" :modal="false">
        <div v-if="openTaskSheet" class="fixed inset-0 z-40 bg-black/70"></div>
        <SheetContent class="w-full max-w-full gap-2" :trap-focus="false" :disable-outside-pointer-events="false">
            <SheetHeader>
                <SheetTitle>Edit task</SheetTitle>
                <!-- <SheetDescription> Make changes to your task here. </SheetDescription> -->
            </SheetHeader>

            <ScrollArea class="h-4/5">
                <div class="mb-2 flex w-full items-center justify-between rounded-sm p-4 shadow dark:bg-zinc-900">
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
                            :class="`${activeTask.is_completed ? 'line-through' : ''} min-h-[0px] w-full resize-none border-0`"
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

                <div class="mb-2 flex w-full items-center justify-between rounded-sm p-2 shadow dark:bg-zinc-900">
                    <DatePicker
                        placeholder="Add due date"
                        id="due_date"
                        title="Due"
                        class="w-full border-0"
                        v-model="selectedDueDate"
                        @update:modelValue="editTask(activeTask.uuid)"
                    />
                </div>

                <div class="mb-2 flex w-full items-center justify-between rounded-sm p-2 shadow dark:bg-zinc-900">
                    <Select :multiple="true" v-model="form.categories" @update:modelValue="editTask(activeTask.uuid)">
                        <SelectTrigger class="w-full border-0">
                            <div class="flex items-center gap-2">
                                <Tag class="me-2 h-4 w-4" />
                                <SelectValue placeholder="Select a category" />
                            </div>
                        </SelectTrigger>
                        <SelectContent class="dark:bg-zinc-900">
                            <SelectGroup>
                                <!-- <SelectLabel>Fruits</SelectLabel> -->
                                <SelectItem
                                    v-for="option in categories"
                                    :key="option.value"
                                    :value="option.value"
                                    :class="`text-${option.value}-500`"
                                >
                                    <CircleIcon :class="`text-${option.value}-500 h-1 w-1`" /> {{ option.label }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                </div>

                <div class="mb-2 flex w-full items-center justify-between rounded-sm p-2 shadow dark:bg-zinc-900">
                    <div class="relative w-full max-w-sm items-center">
                        <Input
                            type="file"
                            id="file"
                            class="w-full resize-none border-0 pl-12"
                            name="file"
                            autoComplete="off"
                            placeholder="Add file"
                        />
                        <span class="absolute inset-y-0 start-0 flex items-center justify-center px-2">
                            <File class="ms-2 size-4 text-muted-foreground" />
                        </span>
                    </div>
                </div>

                <div class="mb-2 flex w-full items-center justify-between rounded-sm p-2 shadow dark:bg-zinc-900">
                    <Textarea
                        id="note"
                        class="w-full resize-none border-0"
                        name="description"
                        v-model="form.note"
                        @blur="editTask(activeTask.uuid)"
                        @keydown.enter.prevent="editTask(activeTask.uuid)"
                        autoComplete="off"
                        placeholder="Add note"
                    />
                </div>

                <div class="mb-2 flex w-full items-center justify-between rounded-sm p-2 shadow dark:bg-zinc-900">
                    <Combobox v-model="selectedList" by="label" class="w-full" @update:modelValue="copyTask(activeTask.uuid)">
                        <ComboboxAnchor as-child>
                            <ComboboxTrigger as-child>
                                <Button variant="outline" class="flex w-full items-center gap-2">
                                    <Copy class="ms-1 me-2 size-4 text-muted-foreground" />

                                    <span class="flex-1 text-left">Copy task to....</span>
                                    <ChevronsUpDown class="h-4 w-4 shrink-0 opacity-50" />
                                </Button>
                            </ComboboxTrigger>
                        </ComboboxAnchor>

                        <ComboboxContent class="z-50">
                            <ComboboxList>
                                <div class="relative w-full max-w-sm items-center">
                                    <ComboboxInput
                                        @input="(e: any) => searchLists(e.target.value)"
                                        class="h-10 rounded-none border-0 border-b focus-visible:ring-0"
                                        placeholder="Select list..."
                                    />
                                </div>

                                <ComboboxEmpty> No lists found. </ComboboxEmpty>

                                <ComboboxGroup>
                                    <ComboboxItem v-for="list in lists" :key="'copy_' + list.id" :value="list.id">
                                        <List />
                                        {{ list.name }}
                                        <ComboboxItemIndicator>
                                            <Check :class="cn('ml-auto h-4 w-4')" />
                                        </ComboboxItemIndicator>
                                    </ComboboxItem>
                                </ComboboxGroup>
                            </ComboboxList>
                        </ComboboxContent>
                    </Combobox>
                </div>

                <div class="flex w-full items-center justify-between rounded-sm p-2 shadow dark:bg-zinc-900">
                    <Combobox v-model="selectedList" by="label" class="w-full" @update:modelValue="moveTask(activeTask.uuid)">
                        <ComboboxAnchor as-child>
                            <ComboboxTrigger as-child>
                                <Button variant="outline" class="flex w-full items-center gap-2">
                                    <ListEndIcon class="ms-1 me-2 size-4 text-muted-foreground" />

                                    <span class="flex-1 text-left">Move task to....</span>
                                    <ChevronsUpDown class="h-4 w-4 shrink-0 opacity-50" />
                                </Button>
                            </ComboboxTrigger>
                        </ComboboxAnchor>

                        <ComboboxContent class="z-50">
                            <ComboboxList>
                                <div class="relative w-full max-w-sm items-center">
                                    <ComboboxInput
                                        @input="(e: any) => searchLists(e.target.value)"
                                        class="h-10 rounded-none border-0 border-b focus-visible:ring-0"
                                        placeholder="Select list..."
                                    />
                                </div>

                                <ComboboxEmpty> No lists found. </ComboboxEmpty>

                                <ComboboxGroup>
                                    <ComboboxItem v-for="list in lists" :key="'move_' + list.id" :value="list.id">
                                        <List />
                                        {{ list.name }}
                                        <ComboboxItemIndicator>
                                            <Check :class="cn('ml-auto h-4 w-4')" />
                                        </ComboboxItemIndicator>
                                    </ComboboxItem>
                                </ComboboxGroup>
                            </ComboboxList>
                        </ComboboxContent>
                    </Combobox>
                </div>
            </ScrollArea>

            <SheetFooter class="!flex !flex-row !items-center !justify-between !space-y-0">
                <SheetClose as-child>
                    <Button type="button" variant="ghost"> <PanelRightClose /> </Button>
                </SheetClose>

                <div class="flex-1 text-center">
                    <small v-if="activeTask.completed_at"> Completed on {{ activeTask.completed_at }} </small>
                    <small v-else> Created on {{ activeTask.created_at }} </small>
                </div>

                <AlertDialog>
                    <AlertDialogTrigger as-child>
                        <Button variant="ghost" class="cursor-pointer text-[var(--destructive)]"> <Trash2 /> </Button>
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
            </SheetFooter>
        </SheetContent>
    </Sheet>
</template>
