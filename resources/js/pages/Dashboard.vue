<script setup lang="ts">
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { List, ListChecks, ListTodo, NotebookText } from 'lucide-vue-next';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

defineProps<{ totalProject: any; totalTask: any; totalDraftTask: any; totalDoneTask: any }>();
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-2 md:p-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <Card class="h-full">
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium"> Total Project </CardTitle>
                            <NotebookText class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-4xl font-bold md:text-5xl">{{ totalProject }}</div>
                        </CardContent>
                    </Card>
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <Card class="h-full">
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium"> Total Task </CardTitle>
                            <List class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-4xl font-bold md:text-5xl">{{ totalTask }}</div>
                        </CardContent>
                    </Card>
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <Card class="h-full">
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium"> Uncompleted Task </CardTitle>
                            <ListTodo class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-4xl font-bold text-[var(--destructive)] md:text-5xl">{{ totalDraftTask }}</div>
                        </CardContent>
                    </Card>
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <Card class="h-full">
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium"> Completed Task </CardTitle>
                            <ListChecks class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-4xl font-bold text-[var(--primary)] md:text-5xl">{{ totalDoneTask }}</div>
                        </CardContent>
                    </Card>
                </div>
            </div>
            <div
                class="relative mt-2 flex min-h-[300px] flex-1 items-center justify-center rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-900"
            >
                <Card class="flex h-full w-full flex-col items-center justify-center p-6">
                    <CardHeader class="flex w-full flex-row items-center justify-between pb-2">
                        <CardTitle class="text-base font-semibold">Project & Task Chart</CardTitle>
                    </CardHeader>
                    <CardContent class="flex w-full items-center justify-center">
                        <!-- Dummy Chart SVG -->
                        <svg width="100%" height="120" viewBox="0 0 300 120" fill="none">
                            <rect x="0" y="0" width="300" height="120" rx="12" fill="#f3f4f6" />
                            <polyline fill="none" stroke="#6366f1" stroke-width="3" points="10,100 50,80 90,60 130,70 170,40 210,60 250,30 290,50" />
                            <circle cx="10" cy="100" r="4" fill="#6366f1" />
                            <circle cx="50" cy="80" r="4" fill="#6366f1" />
                            <circle cx="90" cy="60" r="4" fill="#6366f1" />
                            <circle cx="130" cy="70" r="4" fill="#6366f1" />
                            <circle cx="170" cy="40" r="4" fill="#6366f1" />
                            <circle cx="210" cy="60" r="4" fill="#6366f1" />
                            <circle cx="250" cy="30" r="4" fill="#6366f1" />
                            <circle cx="290" cy="50" r="4" fill="#6366f1" />
                        </svg>
                        <div class="absolute top-4 right-6 text-xs text-muted-foreground">Dummy Chart</div>
                    </CardContent>
                </Card>
            </div>
            <div class="relative mt-2 h-full flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <PlaceholderPattern />
            </div>

            <!-- Recent Activity Section -->
            <div class="mt-4">
                <h2 class="mb-2 text-lg font-semibold">Recent Activity</h2>
                <ul class="space-y-2">
                    <li class="flex items-center gap-2">
                        <span class="inline-block h-2 w-2 rounded-full bg-primary"></span>
                        <span class="text-sm">Task <b>"Design Homepage"</b> marked as completed.</span>
                        <span class="ml-auto text-xs text-muted-foreground">2 hours ago</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="inline-block h-2 w-2 rounded-full bg-destructive"></span>
                        <span class="text-sm">New task <b>"Fix Login Bug"</b> added.</span>
                        <span class="ml-auto text-xs text-muted-foreground">5 hours ago</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="inline-block h-2 w-2 rounded-full bg-primary"></span>
                        <span class="text-sm">Project <b>"Website Redesign"</b> created.</span>
                        <span class="ml-auto text-xs text-muted-foreground">1 day ago</span>
                    </li>
                </ul>
            </div>

            <!-- Due Date Task with Calendar Section -->
            <div class="mt-6">
                <h2 class="mb-2 text-lg font-semibold">Due Date Task</h2>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <!-- Calendar Placeholder -->
                    <div class="rounded-xl border bg-white p-4 dark:bg-gray-900">
                        <div class="mb-2 font-medium">Calendar</div>
                        <!-- Replace with actual calendar component if available -->
                        <div class="flex h-48 items-center justify-center text-muted-foreground">[Calendar Component Here]</div>
                    </div>
                    <!-- Upcoming Due Tasks -->
                    <div class="rounded-xl border bg-white p-4 dark:bg-gray-900">
                        <div class="mb-2 font-medium">Upcoming Tasks</div>
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
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
