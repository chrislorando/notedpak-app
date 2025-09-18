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
import { customFormatDate, dateValueToString } from '@/lib/date';
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
    Star,
    StickyNote,
    Tag,
    Trash2,
    X,
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
const props = defineProps<{ tasks: any; categoryOptions: any; listOptions: any }>();
const page = usePage();

const search = ref<string>(String(page.props.search ?? ''));
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
    attachment: '',
});

watch(
    () => page.props.search,
    (search: any) => {
        if (search) {
            breadcrumbs.value = [
                {
                    title: `Searching for "${search}"`,
                    href: `#`,
                    color: '#ffffff',
                },
            ];

            // initProject.value = project;
            // form.project_uuid = initProject.value?.uuid;
            // form.reset('description');

            // console.log(project);
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

function uploadTaskFile(e: any, uuid: string) {
    const data = new FormData();
    // data.append('_method', 'put');
    data.append('attachment', e.target.files[0]);
    console.log('UPLOAD', data);
    router.post(route('tasks.upload-file', uuid), data, {
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
                setActiveTask(uuid);
            }
        },
    });
}

function deleteFile(uuid: string, id: string) {
    router.delete(route('tasks.delete-file', id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: function (result) {
            if (openTaskSheet.value) {
                setActiveTask(uuid);
            }
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
    // const task = [...props.draftTasks, ...props.completedTasks].find((t) => t.uuid === uuid);
    // activeTask.value = task;
    // form.description = task.description || '';
    // form.note = task.note || '';
    // form.categories = task.categories ? JSON.parse(task.categories).map((cat: any) => cat.value ?? cat) : [];
    // if (task.due_date) {
    //     selectedDueDate.value = stringToDateValue(task.due_date);
    // }
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
    // router.get(
    //     route('tasks.show', initProject.value?.uuid),
    //     {
    //         sort: sort.value,
    //     },
    //     {
    //         preserveScroll: true,
    //         preserveState: true,
    //     },
    // );
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
    // console.log('COPY', props.project);
    // router.patch(
    //     route('projects.copy', props.project.uuid),
    //     {},
    //     {
    //         preserveScroll: true,
    //         preserveState: true,
    //         onSuccess: function (result) {
    //             selectedList.value = '';
    //             lists.value = props.listOptions;
    //             toast.success('List has been copied', {
    //                 description: Date().toString(),
    //                 icon: Check,
    //                 duration: 5000,
    //             });
    //         },
    //     },
    // );
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
                <div class="flex w-[calc(100%-20px)] flex-col gap-2">
                    <ul>
                        <li
                            @click.stop="showSheet(item.uuid)"
                            v-bind:key="item.id"
                            v-for="item in tasks"
                            class="mb-1 flex items-center justify-between rounded-sm border p-5 shadow dark:bg-zinc-900"
                        >
                            <div class="flex items-start space-x-3">
                                <Checkbox
                                    @click.stop
                                    @update:model-value="completeTask(item.uuid)"
                                    class="rounded-2xl border-foreground"
                                    :style="{ borderColor: item.project.color }"
                                />
                                <label class="text-sm leading-4 font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                    <span :class="`${item.is_completed ? 'line-through' : ''}`">{{ item.description }}</span>
                                    <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                        <!-- Project -->
                                        <span v-if="item.project.name" class="flex items-center gap-1 text-gray-400">
                                            <ListIcon class="h-3.5 w-3.5" />
                                            {{ item.project.name }}
                                        </span>

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

                            <button type="button" class="text-gray-400 hover:text-yellow-500" @click.stop="bookmarkTask(item.uuid)">
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
                        @update:model-value="completeTask(activeTask.uuid)"
                        class="me-4 items-center rounded-2xl border-foreground"
                        :default-value="activeTask.is_completed ? true : false"
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
                        <Star :size="18" />
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
                            @change="(e: any) => uploadTaskFile(e, activeTask.uuid)"
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
                                        <div class="0 relative inline-flex h-10 w-10 items-center justify-center overflow-hidden rounded-lg">
                                            <span class="text-sm font-medium text-gray-900 uppercase">{{ file.extension }}</span>
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ file.name }}</p>
                                        <p class="truncate text-sm text-gray-500 dark:text-gray-400">{{ file.size }}KB</p>
                                    </div>
                                    <div class="inline-flex items-center text-base font-semibold text-gray-900 dark:text-white">
                                        <Button variant="ghost" class="cursor-pointer" @click="deleteFile(activeTask.uuid, file.id)"><X /></Button>
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
