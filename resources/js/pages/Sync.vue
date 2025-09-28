<script setup>
import Progress from '@/components/ui/progress/Progress.vue';
import { router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const progress = ref(0);
const status = ref('running');

const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const pollStatus = async () => {
    try {
        const res = await fetch('/syncs/get-status');
        const data = await res.json();
        progress.value = data.progress;
        status.value = data.status;
        if (data.status === 'done') {
            setTimeout(() => {
                router.visit('/dashboard');
            }, 1200);
        } else {
            setTimeout(pollStatus, 1000);
        }
    } catch (e) {
        setTimeout(pollStatus, 2000);
    }
};

onMounted(() => {
    fetch('/syncs/start', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json',
        },
    });
    pollStatus();
});
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center">
        <div class="w-full max-w-md rounded-lg p-8 text-center shadow">
            <Progress v-model="progress" class="w-full" />
            <div class="mt-4 mb-2">{{ progress }}%</div>
            <div v-if="status === 'running'" class="text-gray-500">Syncing data, please wait...</div>
            <div v-else class="font-semibold text-green-600">Sync complete! Redirecting...</div>
        </div>
    </div>
</template>
