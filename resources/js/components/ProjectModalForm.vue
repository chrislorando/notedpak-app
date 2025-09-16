<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

// Components
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Check, Edit, Plus } from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import 'vue-sonner/style.css';
import { SidebarGroup, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from './ui/sidebar';

// const passwordInput = ref<HTMLInputElement | null>(null);

const props = defineProps<{
    isNewRecord?: boolean;
    data?: any;
}>();

const isDialogOpen = ref(false);

const form = useForm({
    uuid: '',
    name: 'Untitled list',
    description: '',
    color: '#ffffff',
});

watch(isDialogOpen, (open) => {
    if (open && props.data) {
        form.uuid = props.data.uuid ?? '';
        form.name = props.data.name ?? '';
        form.description = props.data.description ?? '';
        form.color = props.data.color ?? '#ffffff';
    } else {
        form.clearErrors();
        form.reset();
    }
});

const createList = (e: Event) => {
    e.preventDefault();

    form.post(route('projects.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: function () {
            console.log('SUCCESS');
            toast.success('List has been created', {
                description: Date().toString(),
                icon: Check,
                duration: 5000,
                // action: {
                //     label: 'Ok',
                //     onClick: () => console.log('OK'),
                // },
            });

            closeModal();
        },
        // onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const editList = (e: Event) => {
    e.preventDefault();

    form.put(route('projects.update', form.uuid), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: function () {
            console.log('SUCCESS');
            toast.success('List has been updated', {
                description: Date().toString(),
                icon: Check,
                duration: 5000,
            });

            closeModal();
        },
    });
};

function handleSubmit(e: Event) {
    console.log('isNewRecord', props.isNewRecord);
    if (props.isNewRecord) {
        createList(e);
    } else {
        editList(e);
    }
}

const closeModal = () => {
    isDialogOpen.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <div class="space-y-3">
        <Dialog v-model:open="isDialogOpen">
            <DialogTrigger as-child>
                <SidebarGroup class="px-0 py-0" v-if="props.isNewRecord">
                    <SidebarMenu>
                        <SidebarMenuItem>
                            <SidebarMenuButton asChild>
                                <Button variant="ghost" class="cursor-pointer justify-start gap-2">
                                    <Plus />
                                    <span>New List</span>
                                </Button>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroup>

                <Button variant="ghost" class="cursor-pointer gap-2" v-if="!props.isNewRecord">
                    <Edit />
                    <span class="hidden md:inline-block">Edit List</span>
                </Button>
            </DialogTrigger>
            <DialogContent>
                <form class="space-y-6" @submit.prevent="handleSubmit">
                    <DialogHeader class="space-y-3" v-if="props.isNewRecord">
                        <DialogTitle>New list</DialogTitle>
                        <DialogDescription>Make a new list, project or group activity.</DialogDescription>
                    </DialogHeader>

                    <DialogHeader class="space-y-3" v-if="!props.isNewRecord">
                        <DialogTitle>Edit list</DialogTitle>
                        <DialogDescription>Modify list, project or group activity.</DialogDescription>
                    </DialogHeader>

                    <div class="grid gap-4 py-4">
                        <div class="grid items-center gap-4">
                            <Label for="name" class="text-left"> Name </Label>

                            <Input type="text" id="name" name="name" v-model="form.name" class="col-span-3" :autoComplete="false" />

                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="grid items-center gap-4">
                            <Label for="description" class="text-left"> Description </Label>

                            <Input
                                type="text"
                                id="description"
                                name="description"
                                v-model="form.description"
                                class="col-span-3"
                                autoComplete="false"
                            />

                            <InputError :message="form.errors.description" />
                        </div>

                        <div class="grid items-center gap-4">
                            <Label for="color" class="text-left"> Color </Label>
                            <Input type="color" id="color" name="color" v-model="form.color" class="col-span-3" :autoComplete="false" />

                            <InputError :message="form.errors.color" />
                        </div>
                    </div>

                    <DialogFooter class="gap-2">
                        <DialogClose as-child>
                            <Button variant="secondary"> Cancel </Button>
                        </DialogClose>

                        <Button type="submit" variant="default" :disabled="form.processing" v-if="props.isNewRecord"> Create List </Button>
                        <Button type="submit" variant="default" :disabled="form.processing" v-if="!props.isNewRecord"> Update List </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- <Toaster richColors closeButton theme="dark" /> -->
    </div>
</template>
