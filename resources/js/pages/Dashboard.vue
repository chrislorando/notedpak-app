<script setup lang="ts">
import Alert from '@/components/ui/alert/Alert.vue';
import AlertDescription from '@/components/ui/alert/AlertDescription.vue';
import AlertTitle from '@/components/ui/alert/AlertTitle.vue';
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import BarChart from '@/components/ui/chart-bar/BarChart.vue';
import Progress from '@/components/ui/progress/Progress.vue';
import { ScrollArea } from '@/components/ui/scroll-area';
import AppLayout from '@/layouts/AppLayout.vue';
import { customFormatDate } from '@/lib/date';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { AlertCircle, CalendarDays, List, ListChecks, ListIcon, ListTodo, Loader2, NotebookText } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

onMounted(() => {
    console.log('ADD OVERFLOW 1');
    window.scrollTo(0, 0);
    document.body.classList.remove('overflow-hidden');
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

defineProps<{ totalProject: any; totalTask: any; totalDraftTask: any; totalDoneTask: any; chartData: any; dueDates: any }>();

const progress = ref(0);
let intervalId: number | null = null;

const isLocal = (() => {
    const e = import.meta.env as any;
    return e?.VITE_APP_ENV === 'local' || e?.MODE === 'local' || e?.NODE_ENV === 'local' || e?.NODE_ENV === 'development';
})();

const syncData = () => {
    router.post(route('dashboard.sync'));
    if (!intervalId) {
        intervalId = window.setInterval(checkStatus, 1000);
    }
};

const checkStatus = () => {
    fetch('/dashboard/sync-status')
        .then((res) => res.json())
        .then((data) => {
            progress.value = data.progress || 0;
            if (data.status === 'done') {
                if (intervalId) {
                    clearInterval(intervalId);
                    intervalId = null;
                }
                setTimeout(() => (progress.value = 0), 1000);
            }
        });
};

onMounted(() => {
    // intervalId = setInterval(checkStatus, 1000);
    // console.log('CHECK STATUS ON MOUNT');
    fetch('/dashboard/sync-status')
        .then((res) => res.json())
        .then((data) => {
            if (data.status === 'running') {
                progress.value = data.progress || 0;
                intervalId = window.setInterval(checkStatus, 1000);
            }
        });
});

onUnmounted(() => {
    if (intervalId) clearInterval(intervalId);
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <ScrollArea class="h-[calc(100vh-80px)] rounded-md border-0">
            <div class="flex flex-1 flex-col gap-4 rounded-xl p-2 md:p-4">
                <Alert
                    v-if="isLocal"
                    variant="default"
                    class="flex w-full flex-col items-start justify-between text-primary lg:flex-row lg:items-center"
                >
                    <div class="flex w-full flex-col items-start gap-2 lg:flex-row lg:items-center">
                        <AlertCircle class="mt-0.5 h-4 w-4 shrink-0" />
                        <div class="w-full">
                            <AlertTitle>Info</AlertTitle>
                            <AlertDescription class="w-full" v-if="progress === 0">
                                <span> Click here to temporarily sync data to the server while the auto-sync feature is being developed. </span>
                            </AlertDescription>
                            <Progress v-else v-model="progress" class="w-full" />
                        </div>
                    </div>

                    <Button
                        v-if="progress === 0"
                        @click="syncData"
                        class="ms-0 mt-2 rounded px-3 py-1.5 text-sm font-medium text-white disabled:opacity-50 lg:ms-4 lg:mt-0"
                    >
                        <span>Sync</span>
                    </Button>

                    <Button
                        v-else
                        @click="syncData"
                        class="ms-0 mt-2 rounded px-3 py-1.5 text-sm font-medium text-white disabled:opacity-50 lg:ms-4 lg:mt-0"
                    >
                        <Loader2 class="animate-spin" />
                    </Button>
                </Alert>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        class="relative aspect-video h-40 w-full overflow-hidden rounded-xl border border-sidebar-border/70 md:h-full dark:border-sidebar-border"
                    >
                        <Card class="h-full">
                            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle class="text-sm font-medium"> Total Project </CardTitle>
                                <NotebookText class="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-4xl font-bold md:text-4xl">{{ totalProject }}</div>
                            </CardContent>
                        </Card>
                    </div>
                    <div
                        class="relative aspect-video h-40 w-full overflow-hidden rounded-xl border border-sidebar-border/70 md:h-full dark:border-sidebar-border"
                    >
                        <Card class="h-full">
                            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle class="text-sm font-medium"> Total Task </CardTitle>
                                <List class="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-4xl font-bold md:text-4xl">{{ totalTask }}</div>
                            </CardContent>
                        </Card>
                    </div>
                    <div
                        class="relative aspect-video h-40 w-full overflow-hidden rounded-xl border border-sidebar-border/70 md:h-full dark:border-sidebar-border"
                    >
                        <Card class="h-full">
                            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle class="text-sm font-medium"> Draft Task </CardTitle>
                                <ListTodo class="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-4xl font-bold text-[var(--destructive)] md:text-4xl">{{ totalDraftTask }}</div>
                            </CardContent>
                        </Card>
                    </div>
                    <div
                        class="relative aspect-video h-40 w-full overflow-hidden rounded-xl border border-sidebar-border/70 md:h-full dark:border-sidebar-border"
                    >
                        <Card class="h-full">
                            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle class="text-sm font-medium"> Completed Task </CardTitle>
                                <ListChecks class="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-4xl font-bold text-[var(--primary)] md:text-4xl">{{ totalDoneTask }}</div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
                <div class="grid w-full grid-cols-1 gap-4 xl:grid-cols-2">
                    <!-- Pie Chart Dummy -->
                    <Card class="flex h-full min-h-[300px]">
                        <CardHeader class="items-center justify-between pb-2">
                            <CardTitle class="text-base font-semibold">Draft vs Complete</CardTitle>
                        </CardHeader>
                        <CardContent class="flex w-full items-center justify-center">
                            <!-- Dummy Pie Chart  -->

                            <!-- <DonutChart
                                class="h-60 w-full"
                                index="name"
                                :category="'draft'"
                                :data="chartData"
                                :type="'pie'"
                                :colors="chartData.map((item: any) => item.color)"
                                :showLegend="true"
                                :showTooltip="true"
                            /> -->

                            <BarChart
                                :type="'grouped'"
                                :data="chartData"
                                index="name"
                                :categories="['draft', 'complete']"
                                :y-formatter="
                                    (tick, i) => {
                                        return typeof tick === 'number' ? `${new Intl.NumberFormat('us').format(tick).toString()}` : '';
                                    }
                                "
                                :showXAxis="true"
                                :rounded-corners="4"
                                :showLegend="true"
                            />

                            <!-- <div class="mt-2 text-xs text-muted-foreground">Dummy Pie Chart</div> -->
                        </CardContent>
                    </Card>
                    <!-- Upcoming Due Tasks -->
                    <Card class="flex h-full min-h-[300px]">
                        <CardHeader class="items-center justify-between">
                            <CardTitle class="text-base font-semibold">Upcoming Tasks</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ul class="space-y-2">
                                <li class="flex flex-col" v-for="(task, index) in dueDates" :key="index">
                                    <!-- <a class="text-sm font-semibold">{{ task.description }}</a> -->
                                    <Link :href="`tasks/${task.project.id}`" class="text-sm font-semibold" preserveState :preserveScroll="true">
                                        <span>{{ task.description }}</span>
                                    </Link>
                                    <span class="flex items-center gap-1 text-xs text-muted-foreground"
                                        ><ListIcon class="h-3.5 w-3.5" /> {{ task.project.name }}</span
                                    >
                                    <span class="flex items-center gap-1 text-xs text-muted-foreground"
                                        ><CalendarDays class="h-3.5 w-3.5" /> {{ customFormatDate(task.due_date) }}</span
                                    >
                                </li>
                            </ul>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </ScrollArea>
    </AppLayout>
</template>
