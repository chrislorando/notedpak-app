<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
</script>

<template>
    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-sidebar-primary p-1 text-sidebar-primary-foreground">
        <AppLogoIcon class="h-5 w-5 fill-current text-green-400 dark:text-green-400" />
    </div>

    <!-- <div class="flex h-10 w-10 items-center justify-center rounded-xl">
        <AppLogoIcon />
    </div> -->

    <!-- <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">{{ appName }}</span>
    </div> -->

    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="truncate leading-tight font-semibold">{{ appName }}</span>
    </div>
</template>
