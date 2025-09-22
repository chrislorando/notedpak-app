<script setup lang="ts">
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import DonutChart from '@/components/ui/chart-donut/DonutChart.vue';
import { ScrollArea } from '@/components/ui/scroll-area';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { List, ListChecks, ListTodo, NotebookText } from 'lucide-vue-next';
import { onMounted } from 'vue';

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

defineProps<{ totalProject: any; totalTask: any; totalDraftTask: any; totalDoneTask: any }>();

const data = [
    { name: 'Jan', total: Math.floor(Math.random() * 2000) + 500, predicted: Math.floor(Math.random() * 2000) + 500 },
    { name: 'Feb', total: Math.floor(Math.random() * 2000) + 500, predicted: Math.floor(Math.random() * 2000) + 500 },
    { name: 'Mar', total: Math.floor(Math.random() * 2000) + 500, predicted: Math.floor(Math.random() * 2000) + 500 },
    { name: 'Apr', total: Math.floor(Math.random() * 2000) + 500, predicted: Math.floor(Math.random() * 2000) + 500 },
    { name: 'May', total: Math.floor(Math.random() * 2000) + 500, predicted: Math.floor(Math.random() * 2000) + 500 },
    { name: 'Jun', total: Math.floor(Math.random() * 2000) + 500, predicted: Math.floor(Math.random() * 2000) + 500 },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <ScrollArea class="h-[calc(100vh-70px)] rounded-md border-0">
            <div class="flex flex-1 flex-col gap-4 rounded-xl p-2 md:p-4">
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
                                <CardTitle class="text-sm font-medium"> Uncompleted Task </CardTitle>
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
                <div class="grid w-full grid-cols-1 gap-4 md:grid-cols-2">
                    <!-- Pie Chart Dummy -->
                    <Card class="flex h-full min-h-[320px] flex-col items-center justify-center">
                        <CardHeader class="flex w-full flex-row items-center justify-between pb-2">
                            <CardTitle class="text-base font-semibold">Task Status Pie Chart</CardTitle>
                        </CardHeader>
                        <CardContent class="flex w-full items-center justify-center">
                            <!-- Dummy Pie Chart  -->
                            <DonutChart
                                class="w-full"
                                index="name"
                                :category="'total'"
                                :data="data"
                                :type="'pie'"
                                :colors="['red', 'orange', 'yellow', 'green', 'blue', 'indigo', 'purple']"
                            />
                            <div class="mt-2 text-xs text-muted-foreground">Dummy Pie Chart</div>
                        </CardContent>
                    </Card>
                    <!-- Upcoming Due Tasks -->
                    <Card class="flex h-full min-h-[320px] flex-col justify-start p-6">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-base font-semibold">Upcoming Tasks</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ul class="space-y-2">
                                <li class="flex flex-col">
                                    <span class="font-semibold">Prepare Meeting Slides</span>
                                    <span class="text-xs text-muted-foreground">Due: 24 Sep 2025</span>
                                </li>
                                <li class="flex flex-col">
                                    <span class="font-semibold">Update Documentation</span>
                                    <span class="text-xs text-muted-foreground">Due: 25 Sep 2025</span>
                                </li>
                                <li class="flex flex-col">
                                    <span class="font-semibold">Release v2.0</span>
                                    <span class="text-xs text-muted-foreground">Due: 28 Sep 2025</span>
                                </li>
                            </ul>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </ScrollArea>
    </AppLayout>
</template>
