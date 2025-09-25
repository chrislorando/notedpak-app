<script setup lang="ts">
import DatePicker from '@/components/DatePicker.vue';
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
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import {
    CalendarDaysIcon,
    Check,
    ChevronsUpDown,
    Circle,
    CircleIcon,
    Copy,
    File,
    List,
    ListEndIcon,
    ListIcon,
    PanelRightClose,
    SearchX,
    Star,
    StickyNote,
    Tag,
    TextSearchIcon,
    Trash2,
    X,
} from 'lucide-vue-next';
import { ComboboxContent, DateValue } from 'reka-ui';
import { computed, onMounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

// Define props with TypeScript type
const props = defineProps<{ tasks: any; categoryOptions: any; listOptions: any }>();
const page = usePage();

const search = ref<string>(String(page.props.search ?? ''));
const searchValue = computed(() => page.props.search || '');
const hasSearch = computed(() => searchValue.value !== '');

const activeTask = ref();
const openTaskSheet = ref(false);
const lists = ref<any>(props.listOptions);
const categories = ref<any>(props.categoryOptions);
const selectedList = ref<(typeof props.listOptions)[0] | undefined>();
const selectedDueDate = ref<DateValue>();

const breadcrumbs = ref<BreadcrumbItem[]>([
    {
        title: 'Tasks',
        href: 'tasks',
        color: '#ffffff',
    },
]);

const form = useForm({
    description: '',
    note: '',
    due_date: null as string | null,
    categories: '',
    project_id: '',
    attachment: '',
});

onMounted(() => {
    console.log('ADD OVERFLOW 1');
    window.scrollTo(0, 0);
    document.body.classList.add('overflow-hidden');
});

watch(
    () => page.props.search,
    (search: any) => {
        console.log('SEARCH PROP', search);
        if (search) {
            breadcrumbs.value[0].title = `Searching for "${search}"`;
            props.tasks.value = props.tasks.value;
        } else {
            breadcrumbs.value[0].title = `Searching for ""`;
            props.tasks.value = null;
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

function editTask(id: string) {
    form.due_date = selectedDueDate.value ? dateValueToString(selectedDueDate.value) : null;
    form.categories = JSON.stringify(form.categories);
    console.log('categories', form.categories);
    form.put(route('tasks.update', id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            showSheet(id);
        },
    });
}

function uploadTaskFile(e: any, id: string) {
    const data = new FormData();
    data.append('attachment', e.target.files[0]);
    console.log('UPLOAD', data);
    router.post(route('tasks.upload-file', id), data, {
        forceFormData: true,
        onProgress: (progress) => {
            form.progress = progress ?? null;
        },
        preserveScroll: true,
        preserveState: true,
        onSuccess: function (result) {
            console.log('UPLOADED', result);
            form.progress = null;
            if (openTaskSheet.value) {
                setActiveTask(id);
            }
        },
    });
}

function deleteFile(task_id: string, file_id: string) {
    router.delete(route('tasks.delete-file', file_id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: function (result) {
            if (openTaskSheet.value) {
                setActiveTask(task_id);
            }
        },
    });
}

function completeTask(id: string) {
    router.patch(
        route('tasks.complete', id),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: function (result) {
                console.log('COMPLETED', result);
                const audio = new Audio('/servant-bell-ring-2-211683.mp3');
                audio.play();
                if (openTaskSheet.value) {
                    setActiveTask(id);
                }
            },
        },
    );
}

function bookmarkTask(id: string) {
    router.patch(
        route('tasks.bookmark', id),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: function (result) {
                console.log('BOOKMARKED', result);
                if (openTaskSheet.value) {
                    setActiveTask(id);
                }
            },
        },
    );
}

function deleteTask(id: string) {
    router.delete(route('tasks.destroy', id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: function (result) {
            console.log('DESTROYED', result);
            openTaskSheet.value = false;
            // activeTask.value = [];
        },
    });
}

function setActiveTask(id: string) {
    const task = [...props.tasks].find((t) => t.id === id);
    activeTask.value = task;
    form.description = task.description || '';
    form.note = task.note || '';
    form.categories = task.categories ? JSON.parse(task.categories).map((cat: any) => cat.value ?? cat) : [];
    if (task.due_date) {
        selectedDueDate.value = stringToDateValue(task.due_date);
    }
}

function showSheet(id: string) {
    openTaskSheet.value = true;
    setActiveTask(id);
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
    <Head :title="`Searching for ${search}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full w-full flex-1 flex-col gap-1 rounded-xl p-5" style="width: 100%">
            <ScrollArea class="h-[calc(100vh-65px)] w-[calc(100%+20px)] rounded-md border-0 pb-10">
                <div v-if="tasks.length == 0 && hasSearch" class="ms-auto flex flex-col items-center">
                    <SearchX class="mx-auto mt-20 mb-4 h-16 w-16 text-gray-400" />
                    <p class="text-center">
                        We searched high and low <br />
                        but couldn't find what you're looking for.
                    </p>
                </div>

                <div v-else-if="tasks.length == 0 && !hasSearch" class="ms-auto flex flex-col items-center">
                    <TextSearchIcon class="mx-auto mt-20 mb-4 h-16 w-16 text-gray-400" />
                    <p>What are you looking for?</p>
                </div>
                <div class="flex w-[calc(100%-20px)] flex-col gap-2">
                    <ul>
                        <li
                            @click.stop="showSheet(item.id)"
                            v-bind:key="item.id"
                            v-for="item in tasks"
                            class="mb-2 flex items-center justify-between rounded-sm border p-4 shadow dark:bg-zinc-900"
                        >
                            <div class="flex items-start space-x-3">
                                <Checkbox
                                    @click.stop
                                    @update:model-value="completeTask(item.id)"
                                    class="rounded-2xl border-foreground"
                                    :style="{ borderColor: item.project?.color ?? '' }"
                                />
                                <label class="text-sm leading-4 font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                    <span :class="`${item.is_completed ? 'line-through' : ''}`">{{ item.description }}</span>
                                    <div
                                        v-if="item.due_date || item.note || (item.categories && item.categories.length)"
                                        class="mt-2 flex flex-wrap gap-2 text-xs"
                                    >
                                        <!-- Project -->
                                        <span v-if="item.project.name" class="flex items-center gap-1 text-gray-400">
                                            <ListIcon class="h-3.5 w-3.5" />
                                            {{ item.project?.name ?? '' }}
                                        </span>

                                        <!-- Due Date -->
                                        <span v-if="item.due_date" class="flex items-center gap-1 text-gray-400">
                                            <CalendarDaysIcon class="h-3.5 w-3.5" />
                                            Due {{ customFormatDate(item.due_date) }}
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
                                                :class="[
                                                    'flex items-center gap-1 text-xs capitalize',
                                                    categories.find((c: any) => c.value === cat)?.class,
                                                ]"
                                            >
                                                <Circle class="h-2.5 w-2.5" />
                                                {{ cat }} category
                                            </span>
                                        </template>
                                    </div>
                                </label>
                            </div>

                            <button type="button" class="text-gray-400 hover:text-yellow-500" @click.stop="bookmarkTask(item.id)">
                                <Star :size="18" :fill="item.is_important ? item.project.color : ''" />
                            </button>
                        </li>
                    </ul>
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

            <ScrollArea class="h-4/5 px-2">
                <div class="mb-2 flex w-full items-center justify-between rounded-sm p-4 shadow dark:bg-zinc-900">
                    <Checkbox
                        @click.stop
                        @update:model-value="completeTask(activeTask.id)"
                        class="me-4 items-center rounded-2xl border-foreground"
                        :default-value="activeTask.is_completed ? true : false"
                        :style="{ borderColor: activeTask.project.color ?? '' }"
                    />

                    <div class="flex-1">
                        <Textarea
                            id="description"
                            autocomplete="off"
                            :class="`${activeTask.is_completed ? 'line-through' : ''} min-h-[0px] w-full resize-none border-0`"
                            name="description"
                            v-model="form.description"
                            @blur="editTask(activeTask.id)"
                            @keydown.enter.prevent="editTask(activeTask.id)"
                            autoComplete="off"
                        />
                    </div>

                    <button type="button" class="ms-4 text-gray-400 hover:text-yellow-500" @click.stop="bookmarkTask(activeTask.id)">
                        <Star :fill="activeTask.is_important ? activeTask.project.color : ''" :size="18" />
                    </button>
                </div>

                <div class="mb-2 flex w-full items-center justify-between rounded-sm p-2 shadow dark:bg-zinc-900">
                    <DatePicker
                        placeholder="Add due date"
                        id="due_date"
                        title="Due"
                        class="w-full border-0"
                        v-model="selectedDueDate"
                        @update:modelValue="editTask(activeTask.id)"
                    />
                </div>

                <div class="mb-2 flex w-full items-center justify-between rounded-sm p-2 shadow dark:bg-zinc-900">
                    <Select :multiple="true" v-model="form.categories" @update:modelValue="editTask(activeTask.id)">
                        <SelectTrigger class="w-full border-0">
                            <div class="flex items-center gap-2">
                                <Tag class="me-2 h-4 w-4" />
                                <SelectValue placeholder="Select a category" />
                            </div>
                        </SelectTrigger>
                        <SelectContent class="dark:bg-zinc-900">
                            <SelectGroup>
                                <!-- <SelectLabel>Fruits</SelectLabel> -->
                                <SelectItem v-for="option in categories" :key="option.value" :value="option.value" :class="`${option.class}`">
                                    <CircleIcon :class="`${option.class} h-1 w-1`" /> {{ option.label }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                </div>

                <div class="mb-2 flex w-full items-center justify-between rounded-sm px-2 pt-2 shadow dark:bg-zinc-900">
                    <div class="relative flex w-full flex-col">
                        <Input
                            type="file"
                            id="attachment"
                            class="w-full resize-none border-0 pl-12"
                            name="attachment"
                            autoComplete="off"
                            placeholder="Add file"
                            @change="(e: any) => uploadTaskFile(e, activeTask.id)"
                        />

                        <span class="absolute start-0 mt-2.5 flex items-center justify-center px-2">
                            <File class="ms-2 size-4 text-muted-foreground" />
                        </span>

                        <progress v-if="form.progress" :value="form.progress.percentage" max="100" class="mt-2 w-full rounded-full border">
                            {{ form.progress.percentage }}%
                        </progress>

                        <ul v-if="activeTask.attachments" class="ms-2 mt-2 max-w-md divide-y divide-gray-200 dark:divide-gray-700">
                            <li class="py-2 sm:py-2.5" v-for="file in activeTask.attachments">
                                <div class="flex items-center space-x-4 rtl:space-x-reverse">
                                    <div class="shrink-0">
                                        <div
                                            class="relative inline-flex h-10 w-10 items-center justify-center overflow-hidden rounded-lg"
                                            :style="{ background: activeTask.project.color }"
                                        >
                                            <span class="text-sm font-medium text-gray-900 uppercase">{{ file.extension }}</span>
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ file.name }}</p>
                                        <p class="truncate text-sm text-gray-500 dark:text-gray-400">{{ file.size }}KB</p>
                                    </div>
                                    <div class="inline-flex items-center text-base font-semibold text-gray-900 dark:text-white">
                                        <Button variant="ghost" class="cursor-pointer" @click="deleteFile(activeTask.id, file.id)"><X /></Button>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mb-2 flex w-full items-center justify-between rounded-sm p-2 shadow dark:bg-zinc-900">
                    <Textarea
                        id="note"
                        class="w-full resize-none border-0"
                        name="description"
                        v-model="form.note"
                        @blur="editTask(activeTask.id)"
                        @keydown.enter.prevent="editTask(activeTask.id)"
                        autoComplete="off"
                        placeholder="Add note"
                    />
                </div>

                <div class="mb-2 flex w-full items-center justify-between rounded-sm p-2 shadow dark:bg-zinc-900">
                    <Combobox v-model="selectedList" by="label" class="w-full" @update:modelValue="copyTask(activeTask.id)">
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
                    <Combobox v-model="selectedList" by="label" class="w-full" @update:modelValue="moveTask(activeTask.id)">
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
                    <Button type="button" variant="ghost" class="items-center"> <PanelRightClose /> </Button>
                </SheetClose>

                <div class="flex-1 text-center">
                    <small v-if="activeTask.completed_at"> Completed on {{ activeTask.completed_at }} </small>
                    <small v-else> Created on {{ activeTask.created_at }} </small>
                </div>

                <AlertDialog>
                    <AlertDialogTrigger as-child>
                        <Button variant="ghost" class="cursor-pointer items-center text-[var(--destructive)]"> <Trash2 /> </Button>
                    </AlertDialogTrigger>
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>"{{ activeTask.description }}" will be permanently deleted.</AlertDialogTitle>
                            <AlertDialogDescription> This won't be able to undo this action. </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                            <AlertDialogAction @click.stop="deleteTask(activeTask.id)" class="bg-[var(--destructive)] text-white"
                                >Continue</AlertDialogAction
                            >
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </SheetFooter>
        </SheetContent>
    </Sheet>
</template>
