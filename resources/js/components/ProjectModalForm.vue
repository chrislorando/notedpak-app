<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

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
import { Toaster } from '@/components/ui/sonner';
import { Check, Plus } from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import 'vue-sonner/style.css';
import { SidebarGroup, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from './ui/sidebar';

// const passwordInput = ref<HTMLInputElement | null>(null);

const isDialogOpen = ref(false);

const form = useForm({
    name: 'Untitled list',
    description: '',
    color: '#ffffff',
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
                action: {
                    label: 'Ok',
                    onClick: () => console.log('OK'),
                },
            });

            closeModal();
        },
        // onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    isDialogOpen.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <div class="space-y-6">
        <Dialog :open="isDialogOpen">
            <DialogTrigger as-child>
                <SidebarGroup class="px-0 py-0">
                    <SidebarMenu>
                        <SidebarMenuItem>
                            <SidebarMenuButton asChild>
                                <Button variant="ghost" class="justify-start gap-2" @click="isDialogOpen = true">
                                    <Plus />
                                    <span>New List</span>
                                </Button>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroup>
            </DialogTrigger>
            <DialogContent>
                <form class="space-y-6" @submit.prevent="createList">
                    <DialogHeader class="space-y-3">
                        <DialogTitle>New list</DialogTitle>
                        <DialogDescription>Make a new list, project or group activity.</DialogDescription>
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
                            <Button variant="secondary" @click="closeModal"> Cancel </Button>
                        </DialogClose>

                        <Button type="submit" variant="default" :disabled="form.processing"> Create List </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Toaster richColors />
    </div>
</template>
