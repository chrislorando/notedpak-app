<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type User } from '@/types';
import { onMounted, ref } from 'vue';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
    latestToken?: {
        id: string;
        name: string;
        token: string;
        created_at: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const page = usePage();
const user = page.props.auth.user as User;

const form = useForm({
    name: user.name,
    email: user.email,
});

const tokenForm = useForm({
    token_name: '',
});

const generatedToken = ref<string | null>(null);
const tokenInfo = ref<{ name: string; created_at: string; token: string } | null>(null);
const showToken = ref(false);

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};

const generateToken = () => {
    const timestamp = new Date().toLocaleString();
    tokenForm.token_name = `Token ${timestamp}`;

    tokenForm.post(route('profile.generate-token'), {
        preserveScroll: true,
        onFinish: () => {
            // Setelah redirect selesai, ambil latestToken dari page props
            const latestToken = page.props.latestToken as any;
            if (latestToken) {
                tokenInfo.value = {
                    name: latestToken.name,
                    token: latestToken.token,
                    created_at: latestToken.created_at,
                };
                showToken.value = true;
            }
            tokenForm.reset();
        },
    });
};

const copyToken = () => {
    if (tokenInfo.value?.token) {
        navigator.clipboard.writeText(tokenInfo.value.token);
    }
};

onMounted(() => {
    console.log('REMOVE OVERFLOW');
    window.scrollTo(0, 0);
    document.body.classList.remove('overflow-hidden');

    // Load latest token from database
    if (props.latestToken) {
        console.log(props.latestToken);
        tokenInfo.value = {
            name: props.latestToken.name,
            token: props.latestToken.token,
            created_at: props.latestToken.created_at,
        };
        // Show plaintext token for new tokens or recently generated
        generatedToken.value = props.latestToken.token;
        showToken.value = true;
    }
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Profile information" description="Update your name and email address" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" class="mt-1 block w-full" v-model="form.name" required autocomplete="name" placeholder="Full name" />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Your email address is unverified.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                            A new verification link has been sent to your email address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Save</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                        </Transition>
                    </div>
                </form>
            </div>

            <div class="flex flex-col space-y-6">
                <HeadingSmall title="API Token" description="Generate an API token for accessing the application programmatically" />

                <div class="space-y-6">
                    <div class="flex items-center gap-4">
                        <Button type="button" @click="generateToken" :disabled="tokenForm.processing">Generate Token</Button>
                    </div>

                    <div
                        v-if="showToken && tokenInfo?.token"
                        class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-950"
                    >
                        <div class="mb-4">
                            <p class="mb-3 text-sm font-semibold text-blue-900 dark:text-blue-100">
                                Your API Token (Copy it now, you won't see it again)
                            </p>
                            <div class="flex items-center gap-2">
                                <Textarea v-model="tokenInfo.token" readonly class="font-mono text-xs" rows="1" />

                                <Button type="button" @click="copyToken" variant="outline" class="shrink-0">Copy</Button>
                            </div>
                        </div>

                        <div class="border-t border-blue-200 pt-4 dark:border-blue-800">
                            <p class="text-sm text-blue-900 dark:text-blue-100"><strong>Token Name:</strong> {{ tokenInfo.name }}</p>
                            <p class="text-sm text-blue-900 dark:text-blue-100">
                                <strong>Created:</strong> {{ new Date(tokenInfo.created_at).toLocaleString() }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
