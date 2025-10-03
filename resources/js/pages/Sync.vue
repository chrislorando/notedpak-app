<script setup>
import Progress from '@/components/ui/progress/Progress.vue';
import { router, usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const progressP = ref(0);
const status = ref('running');

const page = usePage();
const token = ref(String(page.props.csrfToken ?? ''));

const pollStatus = async () => {
    try {
        const res = await fetch('/syncs/get-status');
        const data = await res.json();
        progressP.value = data.progress;
        status.value = data.status;
        if (data.status === 'done') {
            console.log('Sync complete, redirecting...');
            setTimeout(() => {
                router.clearHistory();
                router.visit('/dashboard', { replace: true });
                // progress.finish();
            }, 1200);
        } else {
            console.log('Polling again...');
            setTimeout(pollStatus, 1000);
            // progress.start();
        }
    } catch (e) {
        console.error('Error fetching sync status:', e);
        // alert(e.message);
        setTimeout(pollStatus, 2000);
    }
};

onMounted(() => {
    fetch('/syncs/start', {
        method: 'POST',
        headers: {
            _token: token.value,
            'X-CSRF-TOKEN': token.value,
            'Content-Type': 'application/json',
        },
        credentials: 'same-origin',
    });
    // alert('Sync started');

    pollStatus();
});
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center">
        <div class="w-full max-w-md rounded-lg p-8 text-center shadow">
            <Progress v-model="progressP" class="w-full" />
            <div class="mt-4 mb-2">{{ progressP }}%</div>
            <div v-if="status === 'running'" class="text-gray-500 capitalize">{{ status }} : Syncing data, please wait...</div>
            <div v-else class="font-semibold text-green-600 capitalize">{{ status }} : Sync complete! Redirecting...</div>
        </div>
    </div>
</template>
